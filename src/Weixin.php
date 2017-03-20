<?php
/**
 * Created by PhpStorm.
 * User: Meathill
 * Date: 2017/3/20
 * Time: 12:42
 */

namespace MasterMeat;


use MasterMeat\admin\Menu;

class Weixin {

  public function __construct($entry) {
    $this->init_hooks($entry);
  }


  private function init_hooks($entry) {
    $dir = substr($entry, 0, strrpos($entry, '/'));
    $worker = new Worker();
    $menu = new Menu($dir);
    register_activation_hook($entry, [$worker, 'install']);
    add_action('plugins_loaded', [$worker, 'checkDB']);
    add_action('admin_menu', [$menu, 'init']);
  }

  static $instance;

  static public function getInstance($entry) {
    if (is_null(self::$instance)) {
      self::$instance = new self($entry);
    }
    return self::$instance;
  }
}