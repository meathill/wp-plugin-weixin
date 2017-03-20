<?php

/**
 * Created by PhpStorm.
 * User: Meathill
 * Date: 2017/3/21
 * Time: 0:15
 */
namespace MasterMeat\admin;

class Menu {
  private $dir;

  public function __construct($dir) {
    $this->dir = $dir;
  }

  public function init() {
    add_options_page( '微信助手', '肉大师微信助手', 'manage_options', 'my-unique-identifier', [$this, 'onOptions'] );
  }

  public function onOptions() {
    if (!current_user_can('manage_options')) {
      wp_die('您无权操作此页面');
    }
    readfile($this->dir . '/template/admin.html');
  }
}