<?php
/**
 * Created by PhpStorm.
 * User: Meathill
 * Date: 2017/3/29
 * Time: 11:13
 */

namespace MasterMeat;


use DOMDocument;
use DOMElement;

class Document extends DOMDocument {
  const META = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';

  public function __construct($content) {
    parent::__construct('1.0', 'UTF-8');
    @$this->loadHTML(self::META . $content);
  }

  /**
   * @param DOMElement $img
   * @param string $className
   * @return string
   */
  public function addClass($img, $className) {
    $classes = $img->getAttribute('class');
    $classes = $classes ? explode(' ', $classes) : [];
    $classes[] = $className;
    $img->setAttribute('class', implode(' ', $classes));
    return $img;
  }
}