<?php

namespace SMVC\Helpers;

class Text {

  public static function excerpt($text, $length = 200, $append = ' &hellip;') {
    $text = str_replace('><', '> <', $text);
    $text = strip_tags($text);
    $text = preg_replace('/\s+/', ' ', $text);

    if(mb_strlen($text) < $length) {
      return $text;
    } else {
      $extract = mb_substr($text, 0, $length);
      $last_space = strrpos($extract, ' ');
      $extract = mb_substr($extract, 0, $last_space);
      return $extract.$append;
    }
  }

  public static function startsWith($haystack = null, $needle = null, $exact = true) {
    if(empty($haystack) || empty($needle)) {
      return false;
    }

    $length = mb_strlen($needle);
    $substr = mb_substr($haystack, 0, $length);
    return $exact ? $substr === $needle : $substr == $needle;
  }

  public static function endsWith($haystack = null, $needle = null, $exact = false) {
    if(empty($haystack) || empty($needle)) {
      return false;
    }

    $length = mb_strlen($needle);
    $substr = mb_substr($haystack, -$length);
    return $exact ? $substr === $needle : $substr == $needle;
  }

  public static function randomString(
    $type = 'alnum',
    $length = 8,
    $duplicates = false
  ) {

    // character sets
    $lower = 'abcdefghijklmnopqrstuvwxyz';
    $alpha = $lower.mb_strtoupper($lower);
    $numeric = '1234567890';
    $human = '234678bcdefhkmnprstxyACDEFGHJKLMNPRTUVWXYZ';
    $hexdec = '0123456789abcdef';
    $special = '!@#$%^&*()_+~{}[]|:;<>?,./`"\'\\';

    // create pool
    $pool = '';
    switch($type) {
      case 'lower':
        $pool = $lower;
      break;
      case 'alpha':
        $pool = $alpha;
      break;
      case 'alphaspecial':
        $pool = $alpha.$special;
      break;
      case 'numeric':
        $pool = $numeric;
      break;
      case 'human':
        $pool = $human;
      break;
      case 'hexdec':
        $pool = $hexdec;
      break;
      case 'alphanumspecial':
        $pool = $alpha.$numeric.$special;
      break;
      case 'alnum':
      default:
        $pool = $alpha.$numeric;
      break;
    }

    // if length is greater than pool, some must be duplicated
    if($duplicates == false && mb_strlen($pool) < $length) {
      $duplicates = true;
    }

    // generate random string
    $string = '';

    $i = 0;
    while($i < $length) {

      // grab random character
      $char = mb_substr(str_shuffle($pool), 0, 1);

      // check duplicates
      if($duplicates == false) {
        if(!mb_strstr($string, $char)) {
          $string .= $char;
          $i++;
        }
      } else {
        $string .= $char;
        $i++;
      }
    }
    return $string;
  }

  public static function slugify($str, $delimiter = '-') {
    $slug = preg_replace('/[^A-Za-z0-9-]+/', $delimiter, $str);
    $slug = preg_replace('/-+/', '-', $slug);
    return mb_strtolower(trim(trim($slug, $delimiter)));
  }

  // John Michael Doe => JD
  public static function avatarize($str, $length = 2) {
    $names = explode(' ', trim($str));

    if($length > count($names)) {
      $length = count($names);
    }

    $initials = [];

    if(isset($names[0][0])) {
      if($length == 1) {
        $initials[] = $names[0][0];
      } elseif($length == 2) {
        if(isset($names[count($names)-1][0])) {
          $initials[] = $names[0][0].$names[count($names)-1][0];
        }
      } else {
        for ($i = 0; $i < $length; $i++) {
          $initials[] = $names[$i][0];
        }
      }
    }

    return mb_strtoupper(implode('', $initials));
  }

  public static function formatNumber($number) {
    $units = ['', 'K', 'M', 'B', 'T'];
    for($i = 0; $number >= 1000; $i++) {
      $number /= 1000;
    }
    if($i == 0) {
      return number_format($number, 0);
    } else {
      return number_format($number, 1).$units[$i];
    }
  }

  // source: https://stackoverflow.com/a/26163679
  public static function guid() {
    if(function_exists('com_create_guid') === true) {
      return trim(com_create_guid(), '{}');
    }
    return sprintf(
      '%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
      mt_rand(0, 65535),
      mt_rand(0, 65535),
      mt_rand(0, 65535),
      mt_rand(16384, 20479),
      mt_rand(32768, 49151),
      mt_rand(0, 65535),
      mt_rand(0, 65535),
      mt_rand(0, 65535)
    );
  }

}
