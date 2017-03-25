<?php
/**
 * Created by PhpStorm.
 * User: Meathill
 * Date: 2017/3/20
 * Time: 12:42
 */

namespace MasterMeat;


use Exception;
use MasterMeat\admin\Menu;

class Weixin {
  const ID = 'master_meat_weixin';
  const PREFIX = 'mm_weixin_';

  const OUTPUT_TYPE_JSON = 'json';
  const OUTPUT_TYPE_JPEG = 'jpeg';

  public function __construct($entry) {
    $this->init_hooks($entry);
  }

  private function init_hooks($entry) {
    $dir = substr($entry, 0, strrpos($entry, '/') + 1);
    $worker = new Worker();
    $menu = new Menu($dir);
    register_activation_hook($entry, [$worker, 'install']);
    add_action('plugins_loaded', [$worker, 'checkDB']);
    add_action('admin_menu', [$menu, 'init']);

    add_action('admin_post_mm_weixin_fetch_image', [$this, 'fetchImage']);

    add_action('wp_ajax_mm_weixin_save_config', [$this, 'saveConfig']);
    add_action('wp_ajax_mm_weixin_fetch_news_list', [$this, 'fetchNewsList']);
    add_action('wp_ajax_mm_weixin_import_article', [$this, 'importArticle']);

    add_filter('the_content', 'MasterMeat\\Post::removeSRC', 100);
  }

  public function fetchImage() {
    $src = $_REQUEST['src'];
    $date = $_REQUEST['update_time'];
    $image = new Image($src, $date);
    $image->fetch();
    $this->output($image->path, self::OUTPUT_TYPE_JPEG);
  }

  public function fetchNewsList() {
    global $wpdb;

    $token = $this->fetchToken();
    $page = (int)$_REQUEST['page'];
    $api = 'https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token=' . $token;
    $content = Request::post($api, [
      'type' => 'news',
      'offset' => $page * 20,
      'count' => 20
    ]);
    $content = json_decode($content, true);

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

  /**
   * 获取微信公众平台 token
   */
  public function fetchToken() {
    $token = get_option(self::PREFIX . 'token');
    $token = json_decode($token, true);
    if ($token['expires_in'] > time()) {
      return $token['access_token'];
    }

    $app_id = get_option(self::PREFIX . 'app_id');
    $app_secret = get_option(self::PREFIX . 'app_secret');
    $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=${app_id}&secret=${app_secret}";
    $response = file_get_contents($url);
    $response = json_decode($response, true);
    if ($response['errcode']) {
      throw new Exception('fetch token failed', 1000);
    }
    $response['expires_in'] = time() + $response['expires_in'];
    add_option(Weixin::PREFIX . 'token', json_encode($response));
    return $response['access_token'];
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

  private function output($content, $type = self::OUTPUT_TYPE_JSON) {
    switch ($type) {
      case self::OUTPUT_TYPE_JSON:
        if (is_array($content)) {
          $content = json_encode($content);
        }
        header('Content-type: application/json, charset=UTF-8');
        echo $content;
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