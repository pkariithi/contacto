<?php

namespace SMVC\Helpers;

use SMVC\Exceptions\SMVCException;

class Request {

  public $fullUrl;
  public $uri;
  private $isCron = false;

  protected $get = [];
  protected $post = [];
  protected $cookies = [];
  protected $files = [];
  protected $server = [];
  protected $stream = '';

  public function __construct($get, $post, $cookies, $files, $server, $stream = '') {
    $this->get = $get;
    $this->post = $post;
    $this->cookies = $cookies;
    $this->files = $files;
    $this->server = $server;
    $this->stream = $stream;

    $this->loadFullUrl();
    $this->isCronUrl();
  }

  public function getGetParam($key, $defaultValue = false) {
    if(array_key_exists($key, $this->get)) {
      return $this->get[$key];
    }

    return $defaultValue;
  }

  public function getPostParam($key, $defaultValue = false) {
    if(array_key_exists($key, $this->post)) {
      return $this->post[$key];
    }

    return $defaultValue;
  }

  public function getParam($key, $defaultValue = false) {
    if(array_key_exists($key, $this->post)) {
      return $this->post[$key];
    }

    if(array_key_exists($key, $this->get)) {
      return $this->get[$key];
    }

    return $defaultValue;
  }

  public function getFile($key, $defaultValue = false) {
    if(array_key_exists($key, $this->files)) {
      return $this->files[$key];
    }

    return $defaultValue;
  }

  public function getCookie($key, $defaultValue = false) {
    if(array_key_exists($key, $this->cookies)) {
      return $this->cookies[$key];
    }

    return $defaultValue;
  }

  public function getGetParams() {
    return $this->get;
  }

  public function getGetParamsAsUri() {
    if(empty($this->get)) {
      return null;
    }

    $uri_array = [];
    foreach($this->get as $k => $v) {
      $uri_array[] = $k.'='.$v;
    }

    return '?'.implode('&', $uri_array);
  }

  public function getPostParams() {
    return $this->post;
  }

  public function getAllParams() {
    return array_merge($this->get, $this->post);
  }

  public function getStream() {
    return $this->stream;
  }

  public function getCookies() {
    return $this->cookies;
  }

  public function getFiles() {
    return $this->files;
  }

  public function getUri() {
    return $this->getServerVariable('REQUEST_URI');
  }

  public function getPath() {
    return strtok($this->getUri(), '?');
  }

  public function getMethod() {
    return $this->getServerVariable('REQUEST_METHOD');
  }

  public function getHttpAccept() {
    return $this->getServerVariable('HTTP_ACCEPT');
  }

  public function getReferrer() {
    return $this->getServerVariable('HTTP_REFERRER');
  }

  public function getUserAgent() {
    return $this->getServerVariable('HTTP_USER_AGENT');
  }

  public function getQueryString() {
    return $this->getServerVariable('QUERY_STRING');
  }

  public function isHttps() {
    return ($this->getServerVariable('HTTPS') === 'on');
  }

  public function getIpAddress() {
    if(array_key_exists('HTTP_CLIENT_IP', $this->server)) {
      return $this->server['HTTP_CLIENT_IP'];
    }

    if(array_key_exists('HTTP_X_FORWARDED_FOR', $this->server)) {
      return $this->server['HTTP_X_FORWARDED_FOR'];
    }

    if(array_key_exists('REMOTE_ADDR', $this->server)) {
      return $this->server['REMOTE_ADDR'];
    }

    return false;
  }

  private function getServerVariable($key) {
    if(array_key_exists($key, $this->server)) {
      return $this->server[$key];
    }
    return false;
  }

  private function loadFullUrl() {
    $url = $this->isHttps() ? 'https://' : 'http://';
    $url .= $this->getServerVariable('HTTP_HOST');
    $url .= $this->getServerVariable('REQUEST_URI');
    $this->fullUrl = $url;
  }

  // check if is cron request, have base cron url
  public function isCronUrl() {
    if(strpos($this->fullUrl, '/cron/') !== false) {
      $this->isCron = true;
    }
  }

}
