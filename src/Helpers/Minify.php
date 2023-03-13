<?php

namespace SMVC\Helpers;

class Minify {

  public static function html($html) {
    $search = [
        '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
        '/[^\S ]+\</s',     // strip whitespaces before tags, except space
        '/<!--(.|\s)*?-->/' // Remove HTML comments
    ];
    $replace = ['>','<',''];

    $min = preg_replace($search, $replace, trim($html));
    return preg_replace('/(\>)\s*(\<)/m', '$1$2', $min);
  }

}
