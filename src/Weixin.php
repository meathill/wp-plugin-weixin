<?php
/**
 * Created by PhpStorm.
 * User: Meathill
 * Date: 2017/3/20
 * Time: 12:42
 */

namespace MasterMeat;


class Weixin {

  public function __construct() {
    $this->init_hooks();
  }


  private function init_hooks() {
    register_activation_hook(__FILE__, ['Worker', 'install']);
  }

  static $instance;

  static public function getInstance() {
    if (is_null(self::$instance)) {
      self::$instance = new self();
    }
    return self::$instance;
  }
}