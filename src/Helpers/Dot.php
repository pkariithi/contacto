<?php

namespace SMVC\Helpers;

class Dot {

  // convert 'one.two.three = four' to ['one'=>['two'=>['three'=>'four']]]
  public static function toArr($str, $value = false, $sep = '.') {
    $keys = array_reverse(explode($sep, $str));
    $arr = [];
    foreach($keys as $k => $key) {
      if($k == 0) {
        $arr = [$key => $value];
      } else {
        $arr = [$key => $arr];
      }
    }
    return json_decode(json_encode($arr));
  }

  // convert ['one'=>['two'=>['three'=>'four']]] to 'one.two.three = four'
  public static function toStr($array) {
    $ritit = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($array));
    $result = [];
    foreach($ritit as $value) {
      $keys = [];
      foreach (range(0, $ritit->getDepth()) as $depth) {
        $keys[] = $ritit->getSubIterator($depth)->key();
      }
      $result[join('.', $keys)] = $value;
    }
    return (object) $result;
  }

}
