<?php

namespace SMVC\Helpers;

class Icon {

  public static function get($name) {
    $name = str_replace('.svg', '', $name);
    $path = PUBLIC_FOLDER.'assets'.DS.'icons'.DS.'material-icons'.DS.$name.'.svg';
    if(File::exists($path)) {
      return file_get_contents($path);
    }
    return false;
  }

}
