<?php
/**
 * Created by PhpStorm.
 * User: Meathill
 * Date: 2017/3/20
 * Time: 12:42
 */

namespace MasterMeat;


use Exception;
use MasterMeat\admin\Editor;
use MasterMeat\admin\Menu;

class Weixin {
  const ID = 'master_meat_weixin';
  const PREFIX = 'mm_weixin_';

  const OUTPUT_TYPE_JSON = 'json';
  const OUTPUT_TYPE_JPEG = 'jpeg';

  public function __construct($entry) {
    $this->init_components($entry);
    $this->init_hooks($entry);
  }

  private function init_components($entry) {
    $dir = substr($entry, 0, strrpos($entry, '/') + 1);
    $editor = new Editor($dir);
    $menu = new Menu($dir);
  }

  private function init_hooks($entry) {
    $db = new DB();
    register_activation_hook($entry, [$db, 'install']);
    add_action('plugins_loaded', [$db, 'checkDB']);

    add_action('wp_ajax_mm_weixin_save_config', [$this, 'saveConfig']);
    add_action('wp_ajax_mm_weixin_fetch_news_list', [$this, 'fetchNewsList']);
    add_action('wp_ajax_mm_weixin_import_article', [$this, 'importArticle']);

    add_filter('the_content', 'MasterMeat\\Post::removeSRC', 100);
  }

  public function fetchNewsList() {
    global $wpdb;

    $token = Token::fetchToken(true);
    $page = (int)$_REQUEST['page'];
    $api = 'https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token=' . $token;
    $content = Request::post($api, [
      'type' => 'news',
      'offset' => $page * 10,
      'count' => 10
    ]);
    $content = json_decode($content, true);

    // 检查抓取状态
    if (array_key_exists('errcode', $content)) {
      $this->output([
        'code' => 2,
        'msg' => '抓取失败。' .$content['errmsg'],
      ]);
    }

    // 取已经导入过的文章
    $media_ids = array_column($content['item'], 'media_id');
    $placeholder = implode(',', array_fill(0, count($media_ids), '%s'));
    $sql = "SELECT `id`,`post_id`,`title`,`weixin_id`,`fetch_time`
            FROM `{$wpdb->prefix}mm_weixin`
            WHERE `weixin_id` IN ($placeholder)";
    $results = $wpdb->get_results($wpdb->prepare($sql, $media_ids), ARRAY_A);

    // 标记导入过的文章
    $content['item'] = array_map(function ($item) use ($results) {
      $media_id = $item['media_id'];
      $item['content']['news_item'] = array_map(function ($news) use ($media_id, $results) {
        foreach ($results as $log) {
          if ($log['weixin_id'] == $media_id && $log['title'] == $news['title']) {
            $news['post_id'] = $log['post_id'];
            $news['fetch_time'] = $log['fetch_time'];
            break;
          }
        }
        return $news;
      }, $item['content']['news_item']);
      return $item;
    }, $content['item']);

    $this->output($content);
  }

  public function importArticle() {
    $row = $this->getPostData();
    $post = new Post($row);
    $post->insert();

    if ($post->is_ok()) {
      $this->output([
        'code' => 0,
        'msg' => '导入成功',
        'post_id' => $post->ID,
        'fetch_time' => date('Y-m-d H:i:s'),
      ]);
    } else {
      $this->output([
        'code' => 4000,
        'msg' => $post->errors,
      ]);
    }
  }

  public function saveConfig() {
    $app_id = $_REQUEST['app_id'];
    $app_secret = $_REQUEST['app_secret'];

    update_option(Weixin::PREFIX . 'app_id', $app_id);
    update_option(Weixin::PREFIX . 'app_secret', $app_secret);

    $this->output([
      'code' => 0,
      'msg' => '保存成功',
    ]);
  }

  public static function output($content, $type = self::OUTPUT_TYPE_JSON) {
    switch ($type) {
      case self::OUTPUT_TYPE_JSON:
        @wp_send_json([
          'code' => 0,
          'data' => $content,
        ]);
        break;

      case self::OUTPUT_TYPE_JPEG:
        header('Content-Type: image/jpeg');
        header('Content-Length: ' . filesize($content));
        readfile($content);
        break;
    }
    wp_die();
  }

  private function getPostData() {
    $data = file_get_contents('php://input');
    return json_decode($data, true);
  }

  static $instance;

  static public function getInstance($entry) {
    if (is_null(self::$instance)) {
      self::$instance = new self($entry);
    }
    return self::$instance;
  }
}