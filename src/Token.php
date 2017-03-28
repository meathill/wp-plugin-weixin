<?php
/**
 * Created by PhpStorm.
 * User: Meathill
 * Date: 2017/3/29
 * Time: 0:07
 */

namespace MasterMeat;


use Exception;

class Token {
  public static $token;

  /**
   * 获取微信公众平台 token
   */
  public static function fetchToken() {
    if (self::$token) {
      return self::$token;
    }

    $token = get_option(Weixin::PREFIX . 'token');
    $token = json_decode($token, true);
    if ($token['expires_in'] > time()) {
      return $token['access_token'];
    }

    $app_id = get_option(Weixin::PREFIX . 'app_id');
    $app_secret = get_option(Weixin::PREFIX . 'app_secret');
    $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=${app_id}&secret=${app_secret}";
    $response = file_get_contents($url);
    $response = json_decode($response, true);
    if ($response['errcode']) {
      throw new Exception('fetch token failed', 1000);
    }
    $response['expires_in'] = time() + $response['expires_in'];
    add_option(Weixin::PREFIX . 'token', json_encode($response));
    self::$token = $response['access_token'];
    return self::$token;
  }
}