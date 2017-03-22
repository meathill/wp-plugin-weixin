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

    add_action('wp_ajax_mm_weixin_save_config', [$this, 'saveConfig']);
    add_action('wp_ajax_mm_weixin_fetch_news_list', [$this, 'fetchNewsList']);
  }

  public function fetchNewsList() {
    $token = $this->fetchToken();
    $page = (int)$_REQUEST['page'];
    $api = 'https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token=' . $token;
    $content = Request::post($api, [
      'type' => 'news',
      'offset' => $page * 20,
      'count' => 20
    ]);
    $this->output($content);
  }

  /**
   *
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
      throw new Exception('获取 token 失败', 1000);
    }
    $response['expires_in'] = time() + $response['expires_in'];
    add_option(Weixin::PREFIX . 'token', json_encode($response));
    return $response['access_token'];
  }

  public function saveConfig() {
    $app_id = $_REQUEST['app_id'];
    $app_secret = $_REQUEST['app_secret'];

    add_option(Weixin::PREFIX . 'app_id', $app_id);
    add_option(Weixin::PREFIX . 'app_secret', $app_secret);

    $this->output([
      'code' => 0,
      'msg' => '保存成功',
    ]);
  }

  static $instance;

  static public function getInstance($entry) {
    if (is_null(self::$instance)) {
      self::$instance = new self($entry);
    }
    return self::$instance;
  }

  private function output($content, $type = self::OUTPUT_TYPE_JSON) {
    switch ($type) {
      case self::OUTPUT_TYPE_JSON:
        $content = json_encode($content);
        header('Content-type: application/json, charset=UTF-8');
        echo $content;
    }
  }
}