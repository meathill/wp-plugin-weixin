<?php
global $wpdb;
$config = [
  'db' => 'mysql',
  'host' => DB_HOST,
  'port' => '3306',
  'user' => DB_USER,
  'password' => DB_PASSWORD,
  'database' => $wpdb->prefix . 'mm_weixin',
];
return new PDO($config['db'].':host='.$config['host'].';port='.$config['port'].';dbname='.$config['database'], $config['user'], $config['password']);
?>
