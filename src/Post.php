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
      'weixin_id' => $attr['media_id'],
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

  public function insert() {
    require_once ABSPATH . 'wp-admin/includes/image.php';
    // 先抓取所有图片
    list($this->post_content, $images) = $this->replaceIMGSrc();

    // 然后再填入文章
    $this->ID = $this->attr['ID'] = wp_insert_post($this->attr, true);
    if (!$this->is_OK()) {
      $this->errors = $this->ID['errors'];
      return;
    }

    /** @var Image $image */
    foreach ($images as $image) {
      $image->insertAttachment($this->ID);
    }

    // 最后抓取缩略图
    if ($this->post_thumbnail) {
      $this->fetchThumbnail($this->post_thumbnail);
    }

    // 保留记录
    $this->record();
  }

  public function is_OK() {
    return $this->ID && is_int($this->ID);
  }

  /**
   * @param string $src
   * @return Image
   */
  private function fetchImage($src) {
    $image = new Image($src, $this->post_date);
    $image->fetch();
    return $image;
  }

  private function fetchThumbnail($post_thumbnail) {
    $image = $this->fetchImage($post_thumbnail);
    $attachment_id = $image->insertAttachment($this->ID);
    set_post_thumbnail($this->ID, $attachment_id);
  }

  private function replaceIMGSrc() {
    $images = [];
    $doc = new Document($this->post_content);
    $imgs = $doc->getElementsByTagName('img');
    /** @var DOMElement $img */
    foreach ($imgs as $img) {
      $src = $img->getAttribute('data-src');
      if (!$src) {
        continue;
      }
      $image = $this->fetchImage($src);
      $img->setAttribute('data-src', $image->url);
      $img->setAttribute('src', $image->url);
      $doc->addClass($img, 'lazyload');
      $images[] = $image;
    }
    $iframes = $doc->getElementsByTagName('iframe');
    /** @var DOMElement $iframe */
    foreach ($iframes as $iframe) {
      $doc->addClass($iframe, 'lazyload');
    }
    return [$doc->saveHTML(), $images];
  }

  private function record() {
    global $wpdb;

    $table = $wpdb->prefix . 'mm_weixin';
    $now = date('Y-m-d H:i:s');
    $sql = "INSERT INTO `${table}`
            (`weixin_id`,`post_id`,`title`,`fetch_time`)
            VALUE (%s,%d,%s,%s)";
    $wpdb->query($wpdb->prepare($sql, $this->weixin_id, $this->ID, $this->post_title, $now));
  }

  public function upload_imgs($token) {
    $images = get_attached_media('image', $this->ID);
    $doc = new Document($this->post_content);
    $imgs = $doc->getElementsByTagName('img');
    $hostname = get_home_url();
    $is_this_site = "~^${hostname}~";
    /** @var DOMElement $img */
    foreach ($imgs as $img) {
      $src = $img->getAttribute('src');
      if (!preg_match($is_this_site, $src)) {
        continue;
      }
      $attachment = null;
      foreach ($images as $image) {
        if ($image->url == $src) {
          $attachment = $image;
          break;
        }
      }
      if ($attachment && $attachment->description) {
        $newURL = $attachment->description;
      } else {
        $newURL = Request::upload('https://api.weixin.qq.com/cgi-bin/media/uploadimg?access_token='. $token, $attachment->guid);
        wp_update_attachment_metadata($attachment->ID, [
          'description' => $newURL,
        ]);
      }
      $img->setAttribute('src', $newURL);
    }
    $this->post_content = $doc->saveHTML();
  }

  static public function removeSRC($content) {
    $doc = new Document($content);
    $imgs = $doc->getElementsByTagName('img');
    /** @var DOMElement $img */
    foreach ($imgs as $img) {
      if ($img->hasAttribute('data-src')) {
        $img->removeAttribute('src');
      }
    }
    $iframes = $doc->getElementsByTagName('iframe');
    /** @var DOMElement $iframe */
    foreach ($iframes as $iframe) {
      $iframe->removeAttribute('src');
    }
    return $doc->saveHTML();
  }
}