<?php
/**
 * Created by PhpStorm.
 * User: realm
 * Date: 2017/3/21
 * Time: 19:14
 */

namespace MasterMeat;


class Request {

  public static function post($api, $data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
  }
}