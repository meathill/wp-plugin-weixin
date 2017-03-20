<?php
/**
 * Plugin Name: Master Meat Weixin
 * Plugin URI: https://github.com/meathill/wp-plugin-weixin
 * Description: Yet another plugin for WordPress communicating with weixin
 * Version: 1.0
 * Author: Meathill
 * Author URI: http://meathill.com/
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 *
 * Requires at least: 4.7
 * Tested up to: 4.7
 *
 * Created by PhpStorm.
 * User: Meathill
 * Date: 2017/3/19
 * Time: 17:06
 *
 * @package MasterMeat
 * @author Meathill <meathill@gmail.com>
 */
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

require 'vendor/autoload.php';
use MasterMeat\Weixin;

// Global for backwards compatibility.
$GLOBALS['MASTER_MEAT_WEIXIN'] = Weixin::getInstance();
