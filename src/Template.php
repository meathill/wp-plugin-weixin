<?php
/**
 * Created by PhpStorm.
 * User: realm
 * Date: 2017/3/21
 * Time: 18:52
 */

namespace MasterMeat;


class Template {
  private $dir;

  public function __construct($dir) {
    $this->dir = $dir;
  }

  /**
   * @param string $template 模板文件路径
   * @param array $data 用于渲染的数据
   * @return string
   */
  public function render($template, $data) {
    $template = file_get_contents($this->dir . 'template/' . $template);
    $m = new \Mustache_Engine([
      'cache' => '/tmp'
    ]);
    return $m->render($template, $data);
  }
}