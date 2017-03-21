<?php

/**
 * Created by PhpStorm.
 * User: Meathill
 * Date: 2017/3/21
 * Time: 0:15
 */
namespace MasterMeat\admin;

use MasterMeat\Template;
use MasterMeat\Weixin;

class Menu {
  private $dir;

  public function __construct($dir) {
    $this->dir = $dir;
  }

  public function init() {
    add_options_page( '微信助手', '肉大师微信助手', 'manage_options', Weixin::ID, [$this, 'onOptions'] );
  }

  public function onOptions() {
    if (!current_user_can('manage_options')) {
      wp_die('您无权操作此页面');
    }

    $app_id = get_option(Weixin::PREFIX . 'app_id');
    $app_secret = get_option(Weixin::PREFIX . 'app_secret');
    $aes = get_option(Weixin::PREFIX . 'EncodingAESKey');
    $admin_url = admin_url('admin_post.php');
    $template = new Template($this->dir);
    echo $template->render('admin.html', [
      'app_id' => $app_id,
      'app_secret' => $app_secret,
      'EncodingAESKey' => $aes,
    ]);
  }
}