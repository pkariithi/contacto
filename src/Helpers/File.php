<?php

namespace SMVC\Helpers;

use SMVC\Exceptions\SMVCException;

class File {

  public static function exists($filename) {
    return (file_exists($filename) && is_file($filename));
  }

  public static function readable($filename) {
    return self::exists($filename) && is_readable($filename);
  }

  public static function basename($filename) {
    return basename($filename);
  }

  public static function delete($filename) {
    return @unlink($filename);
  }

  public static function rename($filename, $newname) {
    if(self::exists($filename)) {
      return rename($filename, $newname);
    }
    throw new SMVCException(__FILE__, __LINE__, "File '{$filename}' doesn't exist");
  }

  public static function copy($filename, $dest) {
    if(self::exists($filename)) {
      if(!self::exists($dest)) {
        return copy($filename, $dest);
      } else {
        throw new SMVCException(__FILE__, __LINE__, "File '{$dest}' already exists");
      }
    } else {
      throw new SMVCException(__FILE__, __LINE__, "File '{$filename}' doesn't exist");
    }
  }

  public static function extension($filename) {
    return pathinfo($filename, PATHINFO_EXTENSION);
  }

  public static function getFileContents($filename) {
    if(self::exists($filename)) {
      return file_get_contents($filename);
    }
    throw new SMVCException(__FILE__, __LINE__, "File '{$filename}' doesn't exist");
  }

  public static function writeFileContent($content, $filename, $create = true, $append = false, $chmod = 0644) {

    if(!$create && !self::exists($filename)) {
      throw new SMVCException(__FILE__, __LINE__, "File '{$filename}' doesn't exist and cannot be created");
    }

    self::createDir(dirname($filename));

    $handler = $append ? @fopen($filename, 'a') : @fopen($filename, 'w');
    if($handler === false) {
      throw new SMVCException(__FILE__, __LINE__, "File '{$filename}' cannot be opened for writing");
    }

    $error_reporting_level = error_reporting();
    error_reporting(0);

    $write = fwrite($handler, $content);
    if($write === false) {
      throw new SMVCException(__FILE__, __LINE__, "File '{$filename}' writing failed");
    }

    fclose($handler);
    chmod($filename, $chmod);

    error_reporting($error_reporting_level);

    return true;
  }

  public static function size($filename) {
    if(self::exists($filename)) {
      return filesize($filename);
    }
    throw new SMVCException(__FILE__, __LINE__, "File '{$filename}' doesn't exist");
  }

  public static function fileLastChange($filename) {
    if(self::exists($filename)) {
      return filemtime($filename);
    }
    throw new SMVCException(__FILE__, __LINE__, "File '{$filename}' doesn't exist");
  }

  public static function fileLastAccess($filename) {
    if(self::exists($filename)) {
      return fileatime($filename);
    }
    throw new SMVCException(__FILE__, __LINE__, "File '{$filename}' doesn't exist");
  }

  public static function createDir($dir, $chmod = 0755) {
    if(!self::dirExists($dir)) {
      mkdir($dir, $chmod, true);
    }
    chmod($dir, $chmod);
    return true;
  }

  public static function dirExists($dir) {
    return (file_exists($dir) && is_dir($dir));
  }

  public static function getFileList($dir, $path = false) {
    $files = [];
    $iterator = new \DirectoryIterator($dir);
    foreach($iterator as $file) {
      if(!$file->isDot() && $file->isFile()) {
        $files[] = $path ? $file->getPathName() : $file->getFileName();
      }
    }
    return $files;
  }

  public static function getDirList($dir, $path = false) {
    $files = [];
    $iterator = new \DirectoryIterator($dir);
    foreach($iterator as $file) {
      if(!$file->isDot() && $file->isDir()) {
        $files[] = $path ? $file->getPathName() : $file->getFileName();
      }
    }
    return $files;
  }

  public static function download($filename, $displayname = null) {

    if(is_null($displayname)) {
      $displayname = self::basename($filename);
    }

    if(!headers_sent()) {

      if(ini_get('zlib.output_compression')) {
        @ini_set('zlib.output_compression', 'Off');
      }

      // header information
      header('Content-Description: File Transfer');
      header("Content-Type: ".mime_content_type($filename));
      header("Cache-Control: no-cache, must-revalidate");
      header("Expires: 0");
      header('Content-Disposition:attachment;filename="'.$displayname.'"');
      header('Content-Length: ' . filesize($filename));
      header('Pragma: public');

      // clear system output buffer
      ob_clean();
      flush();

      // force download
      readfile($filename);
      return;
    }

  }

  public static function formatFileSize($bytes = null, $decimals = 2, $base = 1024) {

    if(is_null($bytes) || $bytes < 0) {
      return false;
    }

    // allowed bases
    $bases = [1000, 1024];
    if(!in_array($base, $bases)) {
      $base = 1024;
    }

    // edge cases
    if($bytes == 0) {
      return '0 bytes';
    } elseif($bytes == 1) {
      return '1 byte';
    } else if($bytes < $base) {
      return $bytes.' bytes';
    }

    // size labels
    $labels = ['K','M','G','T','P','E','Z','Y'];

    // suffix
    if($base == 1024) {
      $suffix = 'B';
    } elseif($base == 1000) {
      $suffix = 'iB';
    }

    $formatted = null;
    for($i = 0; $i < count($labels) - 1; $i++) {
      if($bytes <= pow($base, $i + 1)) {
        $formatted = number_format($bytes / (pow($base, $i)), $decimals).' '.$labels[$i - 1].$suffix;
        break;
      }
    }

    return $formatted;
  }

}
