<?php

namespace SMVC\Helpers;

class Validate {

  private $instance;
  private $errors = [];

  public static function run($rules) {

    $instance = new Validate();

    $data = [];
    foreach($rules as $field => $params) {
      $data[$field] = $params['value'];
      foreach($params['rules'] as $rule => $vars) {
        $instance->$rule($data[$field], $vars);
      }
    }

    return (object) [
      'success' => empty($instance->errors) ? true : false,
      'data' => $data,
      'errors' => $instance->errors
    ];
  }

  private function required($value, $vars) {
    if(is_array($value) || is_object($value)) {
      if(empty($value)) {
        $this->setError($vars['msg']);
      }
    } else {
      if(empty(trim($value))) {
        $this->setError($vars['msg']);
      }
    }
  }

  private function numeric($value, $vars) {
    if(empty($value)) {
      return;
    }

    $regex = '/^([0-9]+)$/';
    if(!preg_match($regex, $value)) {
      $this->setError($vars['msg']);
    }
  }

  private function in($value, $vars) {
    if(empty($value)) {
      return;
    }

    if(is_array($value) || is_object($value)) {
      $value = (array) $value;
      $diff = array_diff($value, $vars['values']);
      if(count($diff) !== 0) {
        $this->setError($vars['msg']);
      }
    } else {
      if(!in_array($value, $vars['values'])) {
        $this->setError($vars['msg']);
      }
    }
  }

  private function notin($value, $vars) {
    if(empty($value)) {
      return;
    }

    if(is_array($value) || is_object($value)) {
      $value = (array) $value;
      $diff = array_diff($value, $vars['values']);
      if(count($diff) === 0) {
        $this->setError($vars['msg']);
      }
    } else {
      if(in_array($value, $vars['values'])) {
        $this->setError($vars['msg']);
      }
    }
  }

  private function email($value, $vars) {
    if(empty($value)) {
      return;
    }

    if(!filter_var($value, FILTER_VALIDATE_EMAIL)) {
      $this->setError($vars['msg']);
    }
  }

  private function similar($value, $vars) {
    if(empty($value)) {
      return;
    }

    if($value !== $vars['to']) {
      $this->setError($vars['msg']);
    }
  }

  private function url($value, $vars) {
    if(empty($value)) {
      return;
    }

    if(!filter_var($value, FILTER_VALIDATE_URL)) {
      $this->setError($vars['msg']);
    }
  }

  private function msisdn($value, $vars) {
    if(empty($value)) {
      return;
    }

    $regex = '/^(0|\+254|254)(7|1)([0-9]{8})$/';
    if(isset($vars['regex']) && $vars['regex'] != '') {
      $regex = $vars['regex'];
    }

    if(!preg_match($regex, $value)) {
      $this->setError($vars['msg']);
    }
  }

  private function length($value, $vars) {
    if(empty($value)) {
      return;
    }

    if(mb_strlen($value) != $vars['length']) {
      $this->setError($vars['msg']);
    }
  }

  private function minLen($value, $vars) {
    if(empty($value)) {
      return;
    }

    if(mb_strlen($value) < $vars['length']) {
      $this->setError($vars['msg']);
    }
  }

  private function maxLen($value, $vars) {
    if(empty($value)) {
      return;
    }

    if(mb_strlen($value) > $vars['length']) {
      $this->setError($vars['msg']);
    }
  }

  private function regex($value, $vars) {
    if(empty($value)) {
      return;
    }

    if(preg_match($vars['pattern'], $value)) {
      $this->setError($vars['msg']);
    }
  }

  private function date($value, $vars) {
    if(empty($value)) {
      return;
    }

    $d = \DateTime::createFromFormat($vars['format'], $value);
    $isvalid = $d && $d->format($vars['format']) == $value;

    if(!$isvalid) {
      $this->setError($vars['msg']);
    }
  }

  private function dateAfter($value, $vars) {
    if(empty($value)) {
      return;
    }

    $d = \DateTime::createFromFormat($vars['format'], $value);
    $c = \DateTime::createFromFormat($vars['format'], $vars['date']);

    if($c > $d) {
      $this->setError($vars['msg']);
    }
  }

  private function dateBefore($value, $vars) {
    if(empty($value)) {
      return;
    }

    $d = \DateTime::createFromFormat($vars['format'], $value);
    $c = \DateTime::createFromFormat($vars['format'], $vars['date']);

    if($c <= $d) {
      $this->setError($vars['msg']);
    }
  }

  private function setError($msg) {
    $this->errors[] = $msg;
  }

}
