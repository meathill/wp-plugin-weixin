<?php
/**
 * Created by PhpStorm.
 * User: Meathill
 * Date: 2017/3/27
 * Time: 17:43
 */

namespace MasterMeat\admin;


use MasterMeat\Request;
use MasterMeat\Template;
use MasterMeat\Token;
use MasterMeat\Weixin;
use PDO;
use WP_Error;
use WP_Post;

class Editor {
  private $dir;

  const NONCE = 'mm_weixin_editor_nonce';

  const FIELD = 'mm_weixin_is_sync';

  public function __construct($dir) {
    $this->dir = $dir;
    add_action('add_meta_boxes', [$this, 'init']);
    add_action('save_post', [$this, 'save'], 10, 1);
    add_action('publish_post', [$this, 'sync'], 10, 2);
  }

  public function init() {
    add_meta_box(
      'mm_weixin_editor_meta_box',          // this is HTML id of the box on edit screen
      '肉大师微信助手',    // title of the box
      [$this, 'render'],   // function to be called to display the checkboxes, see the function below
      'post',        // on which edit screen the box should appear
      'side',      // part of page where the box should appear
      'default'      // priority of the box
    );
  }

  public function render() {
    $template = new Template($this->dir);
    $is_sync = get_option(Weixin::PREFIX . 'sync');
    wp_nonce_field( plugin_basename( $this->dir ), self::NONCE);
    echo $template->render('editor.html', [
      'is_sync' => $is_sync,
    ]);
  }

  public function save($post_id) {
    // check if this isn't an auto save
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
      return;

    // security check
    if ( !wp_verify_nonce( $_POST[self::NONCE], plugin_basename( $this->dir ) ) )
      return;

    // now store data in custom fields based on checkboxes selected
    if ( isset( $_POST[self::FIELD] ) ) {
      update_post_meta( $post_id, self::FIELD, 1 );
    } else {
      update_post_meta( $post_id, self::FIELD, 0 );
    }
  }

  /**
   * @param $ID
   * @param WP_Post $post
   * @return bool|WP_Error
   */
  public function sync($ID, $post) {
    $is_sync = get_post_meta($ID, self::FIELD, true);
    if (!$is_sync) {
      return;
    }

    global $wpdb;
    /** @var PDO $pdo */
    $pdo = require $this->dir . 'connector/pdo.php';

    $table = $wpdb->prefix . 'mm_weixin';
    $sql = "SELECT `weixin_id`,`status`
            FROM ${table}
            WHERE `post_id`=:id";
    $state = $pdo->prepare($sql);
    $state->execute([':id' => $ID]);
    $row = $state->fetch(PDO::FETCH_ASSOC);
    if ($row['status'] == 0) { // 从微信抓回来的，为避免影响同图文素材内其它文章，不做更新
      return;
    }

    $token = Token::fetchToken();
    $data = [
      'articles' => [
        [
          'title' => $post->post_title,
          'thumb_media_id' => '',
          'author' => $post->post_author,
          'digest' => $post->post_excerpt,
          'show_cover_pic' => 1,
          'content' => $post->post_content,
          'content_source_url' => get_permalink($post),
        ]
      ]
    ];
    if ($row['weixin_id']) {
      $api = 'https://api.weixin.qq.com/cgi-bin/material/update_news?access_token=' . $token;
      $data['media_id'] = $row['weixin_id'];
      $data['index'] = 0;
    } else {
      $api = 'https://api.weixin.qq.com/cgi-bin/material/add_news?access_token=' . $token;
    }

    $result = Request::post($api, $data);
    if (array_key_exists('errcode', $result) && $result['errcode'] != 0) {
      return new WP_Error(30000, $result['errmsg']);
    }
    if ($result['media_id']) {
      $sql = "INSERT INTO ${table}
              (`weixin_id`,`post_id`,`title`,`fetch_time`,`status`)
              VALUE (:media_id, :post_id, :title, :fetch_time, 1)";
      $state = $pdo->prepare($sql);
      $state->execute([
        ':media_id' => $result['media_id'],
        ':post_id' => $ID,
        ':title' => $post->post_title,
        ':fetch_time' => date('Y-m-d H:i:s'),
      ]);
    }
    return true;
  }
}