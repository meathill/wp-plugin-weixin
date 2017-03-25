<?php
/**
 * Created by PhpStorm.
 * User: realm
 * Date: 2017/3/24
 * Time: 18:57
 */

namespace MasterMeat;


class Image {
  /**
   * @var string
   */
  public $url = '';
  /**
   * @var string
   */
  public $path = '';
  /**
   * @var int
   */
  public $attachment_id;
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
    $this->url = $url;
    if (file_exists($filename)) {
      return $url;
    }
    file_put_contents($filename, file_get_contents($this->src));
    return $url;
  }

  public function insertAttachment($parent) {
    $fileType = wp_check_filetype($this->path, null);
    $this->attachment_id = $attachment_id = wp_insert_attachment([
      'guid' => $this->path,
      'post_mime_type' => $fileType['type'],
      'post_title' => substr($this->path, strrpos($this->path, '/')),
      'post_content' => '',
      'post_status' => 'inherit'
    ], $this->path, $parent);

    $attach_data = wp_generate_attachment_metadata($attachment_id, $this->path);
    wp_update_attachment_metadata($attachment_id, $attach_data);

    return $attachment_id;
  }

  private function extractDate($post_date) {
    return date('Y/m', strtotime($post_date));
  }
}