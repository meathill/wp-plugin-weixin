<?php
/**
 * Created by PhpStorm.
 * User: Meathill
 * Date: 2017/3/24
 * Time: 11:23
 */

namespace MasterMeat;
use DOMDocument;
use DOMElement;


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
    if (array_key_exists($name, $this->attr)) {
      return $this->attr[$name];
    }
    return null;
  }

  public function __set($name, $value) {
    $this->attr[$name] = $value;
  }

  public function fetchImage($src, $is_url = true) {
    $image = new Image($src, $this->post_date);
    $image->fetch();
    return $is_url ? $image->url : $image->path;
  }

  public function insert() {
    // 先抓取所有图片
    $this->post_content = $this->replaceIMGSrc();

    // 然后再填入文章
    $this->ID = $this->attr['ID'] = wp_insert_post($this->attr, true);
    if (!$this->is_OK()) {
      $this->errors = $this->ID['errors'];
      return;
    }

    // 最后抓取缩略图
    if ($this->post_thumbnail) {
      $this->fetchThumbnail($this->post_thumbnail);
    }
  }

  public function is_OK() {
    return $this->ID && is_int($this->ID);
  }

  private function fetchThumbnail($post_thumbnail) {
    $path = $this->fetchImage($post_thumbnail, false);
    $this->insertAttachment($path);
  }

  private function insertAttachment($filename) {
    $fileType = wp_check_filetype($filename, null);
    $attachment_id = wp_insert_attachment([
      'guid' => $filename,
      'post_mime_type' => $fileType['type'],
      'post_title' => $this->post_title,
      'post_content' => '',
      'post_status' => 'inherit'
    ], $filename, $this->ID);

    require_once ABSPATH . 'wp-admin/includes/image.php';
    $attach_data = wp_generate_attachment_metadata($attachment_id, $filename);
    wp_update_attachment_metadata($attachment_id, $attach_data);

    set_post_thumbnail($this->ID, $attachment_id);

    return $attachment_id;
  }

  private function replaceIMGSrc() {
    $doc = new DOMDocument('1.0', 'UTF-8');
    $doc->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $this->post_content);
    $imgs = $doc->getElementsByTagName('img');
    /** @var DOMElement $img */
    foreach ($imgs as $img) {
      $src = $img->getAttribute('data-src');
      if (!$src) {
        continue;
      }
      $url = $this->fetchImage($src);
      $img->setAttribute('data-src', $url);
      $img->setAttribute('src', $url);
      $img->setAttribute('class', implode(' ', [$img->getAttribute('class'), 'lazyload']));
    }
    $iframes = $doc->getElementsByTagName('iframe');
    /** @var DOMElement $iframe */
    foreach ($iframes as $iframe) {
      $iframe->setAttribute('class', implode(' ', [$iframe->getAttribute('class'), 'lazyload']));
    }
    return $doc->saveHTML();
  }
}