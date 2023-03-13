<?php

namespace SMVC\Helpers;

use SMVC\Exceptions\SMVCException;

class Cookie {

  private $name;
  private $value;
  private $domain;
  private $path;
  private $maxAge;
  private $isSecure;
  private $isHttpOnly;

  public function __construct($name, $value) {
    $this->name = $name;
    $this->value = $value;
  }

  public function setName($name) {
    $this->name = (string) $name;
  }

  public function getName() {
    return $this->name;
  }

  public function setValue($value) {
    $this->value = (string) $value;
  }

  public function getValue() {
    return $this->value;
  }

  public function setMaxAge($seconds) {
    $this->maxAge = (int) $seconds;
  }

  public function setDomain($domain) {
    $this->domain = (string) $domain;
  }

  public function setPath($path) {
    $this->path = (string) $path;
  }

  public function setSecure($secure) {
    $this->isSecure = (bool) $secure;
  }

  public function setHttpOnly($isHttpOnly) {
    $this->isHttpOnly = (bool) $isHttpOnly;
  }

  public function getHeaders() {

    $parts = [
      $this->name.'='.rawurlencode($this->value),
      $this->getMaxAgeHeader(),
      $this->getExpiresHeader(),
      $this->getDomainHeader(),
      $this->getPathHeader(),
      $this->getSecureHeader(),
      $this->getHttpOnlyHeader()
    ];

    $filtered_parts = array_filter($parts);

    return implode('; ', $filtered_parts);
  }

  public function getHeaderString() {
    $parts = [
      $this->name.' = '.rawurlencode($this->value),
      $this->getMaxAgeString(),
      $this->getExpiresString(),
      $this->getDomainString(),
      $this->getPathString(),
      $this->getSecureString(),
      $this->getHttpOnlyString()
    ];

    $filtered = array_filter($parts);
    return implode(':', $filtered);
  }

  private function getMaxAgeHeader() {
    if(!is_null($this->maxAge)) {
      return 'Max-Age='.$this->maxAge;
    }
  }

  private function getMaxAgeString() {
    if(!is_null($this->maxAge)) {
      return "Max-Age={$this->maxAge}";
    }
  }

  private function getExpiresHeader() {
    if(!is_null($this->maxAge)) {
      return 'Expires='.gmdate("D, d-M-Y H:i:s", time() + $this->maxAge).' UTC';
    }
  }

  private function getExpiresString() {
    if(!is_null($this->maxAge)) {
      $date = gmdate("D, d-M-Y H:i:s", time() + $this->maxAge);
      return "expires={$date} GMT";
    }
  }

  private function getDomainHeader() {
    if(!is_null($this->domain)) {
      return 'domain='.$this->domain;
    }
  }

  private function getDomainString() {
    if($this->domain) {
      return "domain={$this->domain}";
    }
  }

  private function getPathHeader() {
    if(!is_null($this->path)) {
      return 'path='.$this->path;
    }
  }

  private function getPathString() {
    if($this->path) {
      return "path={$this->path}";
    }
  }

  private function getSecureHeader() {
    if($this->isSecure) {
      return 'Secure';
    }
  }

  private function getSecureString() {
    if($this->isSecure) {
      return "secure";
    }
  }

  private function getHttpOnlyHeader() {
    if($this->isHttpOnly) {
      return 'HttpOnly';
    }
  }

  private function getHttpOnlyString() {
    if($this->isHttpOnly) {
      return "HttpOnly";
    }
  }

}
