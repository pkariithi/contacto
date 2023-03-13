<?php

namespace SMVC\Exceptions;

class SMVCException extends \Exception {

  public $file;
  public $line;

  public function __construct($file, $line, $message, $code = 0, \Exception $previous = null) {
    parent::__construct($message, $code, $previous);
  }

  public function __toString() {
    return "[".__CLASS__."] [Code {$this->code}] [{$this->file}:{$this->line}] {$this->message}\n";
  }

}
