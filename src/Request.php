<?php
/**
 * Created by PhpStorm.
 * User: realm
 * Date: 2017/3/21
 * Time: 19:14
 */

namespace MasterMeat;


use CURLFile;

class Request {

  public static function post($api, $data) {
    $ch = curl_init($api);
    curl_setopt($ch, CURLOPT_POST,1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
  }

  public static function upload($api, $src) {
    $fileType = wp_check_filetype($src, null);
    $file = new CURLFile($src, $fileType['type'], 'media');
    $response = self::post($api, [
      'media' => $file,
    ]);
    $response = json_decode($response, true);
    return $response['url'];
  }
}