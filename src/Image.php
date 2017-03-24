<?php
/**
 * Created by PhpStorm.
 * User: realm
 * Date: 2017/3/24
 * Time: 18:57
 */

namespace MasterMeat;


class Image {
  public $url = '';
  public $path = '';

  private $date;
  private $src;

  public function __construct($src, $date) {
    $this->src = $src;
    $this->date = $date;
  }

  public function fetch() {
    if (!$this->src) {
      return $this->url;
    }
    $url = parse_url($this->src);
    parse_str($url['query'], $query);
    $ext = $query['wx_fmt'];

    $dir = wp_upload_dir($this->extractDate($this->date));
    $filename = md5($this->src) . '.' . $ext;
    $url = $dir['url'] . '/' . $filename;
    $filename = $dir['path'] . '/' . $filename;
    $this->path = $filename;
    if (file_exists($filename)) {
      $this->url = $url;
      return $url;
    }
    file_put_contents($filename, file_get_contents($this->src));
    $this->url = $url;
    return $url;
  }

  private function extractDate($post_date) {
    return date('Y/m', strtotime($post_date));
  }
}