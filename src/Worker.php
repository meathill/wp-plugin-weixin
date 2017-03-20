<?php

namespace MasterMeat;

/**
 * Created by PhpStorm.
 * User: Meathill
 * Date: 2017/3/20
 * Time: 12:31
 */
class Worker {
  const DB_VERSION = '1.0';

  public function install() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'mm_weixin';
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE `$table_name` (
            `id` int(10) NOT NULL AUTO_INCREMENT,
            `weixin_id` INT NOT NULL,
            `post_id` INT NOT NULL,
            `fetch_time` DATETIME NOT NULL,
            `status` TINYINT(1) DEFAULT 0,
            PRIMARY KEY `id`
          ) $charset";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    add_option('mm_weixin_db_version', self::DB_VERSION);
  }
}