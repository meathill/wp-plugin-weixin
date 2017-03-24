<?php
/**
 * Created by PhpStorm.
 * User: Meathill
 * Date: 2017/3/24
 * Time: 11:23
 */

namespace MasterMeat;
use Exception;


/**
 * @property string post_thumbnail
 * @property string weixin_id
 * @property string post_title
 * @property string post_content
 * @property string post_date
 */
class Post {
  protected $attr;
  protected $ID;

  public $errors;

  public function __construct($attr) {
    $this->attr = [
      'ID' => $attr['post_id'],
      'weixin_id' => $attr['weixin_id'],
      'post_author' => 1,
      'post_date' => substr($attr['update_time'], 0, 10),
      'post_content' => $attr['content'],
      'post_excerpt' => $attr['digest'],
      'post_title' => $attr['title'],
      'post_thumbnail' => $attr['thumb_url'],
      'post_status' => 'publish',
    ];
  }

  public function __get($name) {
    if (array_key_exists($this->attr, $name)) {
      return $this->attr[$name];
    }
    return null;
  }

  public function __set($name, $value) {
    $this->attr[$name] = $value;
  }

  public function fetchImage() {

  }

  public function insert() {
    // 先抓取缩略图
    $thumbnail_id = 0;
    if ($this->post_thumbnail) {
      $thumbnail_id = $this->fetchThumbnail($this->post_thumbnail, $this->weixin_id);
    }
    // 然后抓取所有图片
    $this->post_content = $this->replaceIMGSrc();

    // 最后再填入文章
    $this->ID = wp_insert_post($this->attr, true);
    if (!$this->is_OK()) {
      $this->errors = $this->ID['errors'];
    }
    set_post_thumbnail($this->ID, $thumbnail_id);
  }

  public function is_OK() {
    return $this->ID && is_int($this->ID);
  }

  private function fetchThumbnail($post_thumbnail, $weixin_id) {
    $dir = wp_upload_dir($this->post_date);
    $filename = "${dir['path']}/${weixin_id}_thumbnail_";
    $count = 0;
    while (file_exists("${filename}${count}.jpg")) {
      $count++;
    }
    $filename = "${filename}${count}.jpg";
    file_put_contents($filename, file_get_contents($this->post_thumbnail));

    $result = wp_insert_attachment([
      'post_title' => $this->post_title
    ], $filename, 0, true);

    if (!is_int($result)) {
      throw new Exception('抓取头图失败。' . json_encode($result['errors']), 400011);
    }
    return $result;
  }

  private function replaceIMGSrc() {
    return preg_replace_callback('~<img[^>]*(?:data-)?src="(.*?)"[^>]*/>~', [$this, 'fetchImage'], $this->post_content);
  }
}