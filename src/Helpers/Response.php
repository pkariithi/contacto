<?php

namespace SMVC\Helpers;

use SMVC\Exceptions\SMVCException;
use SMVC\Helpers\Cookie;
use SMVC\Helpers\Utils;

class Response {

  protected $baseUrl;
  private $version = '1.1';
  private $contentType = 'text/html';
  private $charset = 'UTF-8';
  private $statusCode = 200;
  private $statusText = 'OK';
  private $headers = [];
  private $cookies = [];
  private $content;

  private $statusTexts = [
    100 => "Continue",
    101 => "Switching Protocols",
    102 => "Processing",
    200 => "OK",
    201 => "Created",
    202 => "Accepted",
    203 => "Non-Authoritative Information",
    204 => "No Content",
    205 => "Reset Content",
    206 => "Partial Content",
    207 => "Multi-Status",
    300 => "Multiple Choices",
    301 => "Moved Permanently",
    302 => "Found",
    303 => "See Other",
    304 => "Not Modified",
    305 => "Use Proxy",
    306 => "(Unused)",
    307 => "Temporary Redirect",
    308 => "Permanent Redirect",
    400 => "Bad Request",
    401 => "Unauthorized",
    402 => "Payment Required",
    403 => "Forbidden",
    404 => "Not Found",
    405 => "Method Not Allowed",
    406 => "Not Acceptable",
    407 => "Proxy Authentication Required",
    408 => "Request Timeout",
    409 => "Conflict",
    410 => "Gone",
    411 => "Length Required",
    412 => "Precondition Failed",
    413 => "Request Entity Too Large",
    414 => "Request-URI Too Long",
    415 => "Unsupported Media Type",
    416 => "Requested Range Not Satisfiable",
    417 => "Expectation Failed",
    418 => "I'm a teapot",
    419 => "Authentication Timeout",
    420 => "Enhance Your Calm",
    422 => "Unprocessable Entity",
    423 => "Locked",
    424 => "Failed Dependency",
    424 => "Method Failure",
    425 => "Unordered Collection",
    426 => "Upgrade Required",
    428 => "Precondition Required",
    429 => "Too Many Requests",
    431 => "Request Header Fields Too Large",
    444 => "No Response",
    449 => "Retry With",
    450 => "Blocked by Windows Parental Controls",
    451 => "Unavailable For Legal Reasons",
    494 => "Request Header Too Large",
    495 => "Cert Error",
    496 => "No Cert",
    497 => "HTTP to HTTPS",
    499 => "Client Closed Request",
    500 => "Internal Server Error",
    501 => "Not Implemented",
    502 => "Bad Gateway",
    503 => "Service Unavailable",
    504 => "Gateway Timeout",
    505 => "HTTP Version Not Supported",
    506 => "Variant Also Negotiates",
    507 => "Insufficient Storage",
    508 => "Loop Detected",
    509 => "Bandwidth Limit Exceeded",
    510 => "Not Extended",
    511 => "Network Authentication Required",
    598 => "Network read timeout error",
    599 => "Network connect timeout error"
  ];

  public function setBaseUrl($url) {
    $this->baseUrl = $url;
  }

  public function getBaseUrl() {
    return $this->baseUrl;
  }

  public function setContentType($contentType) {
    switch($contentType) {
      case 'html':
      case 'text/html':
        $contentType = 'text/html';
        break;

      case 'json':
      case 'application/json':
        $contentType = 'application/json';
        break;

      default:
        $contentType = 'text/html';
    }
    $this->contentType = $contentType;
  }

  public function getContentType() {
    return $this->contentType;
  }

  public function setStatusCode($statusCode) {
    if(array_key_exists($statusCode, $this->statusTexts)) {
      $this->statusCode = (int) $statusCode;
      $this->statusText = (string) $this->statusTexts[$statusCode];
    }
  }

  public function setStatus($statusCode, $statusText = null) {
    if($statusText == null && array_key_exists($statusCode, $this->statusTexts)) {
      $statusText = $this->statusTexts[$statusCode];
    }

    $this->statusCode = (int) $statusCode;
    $this->statusText = (string) $statusText;
  }

  public function getStatusCode() {
    return $this->statusCode;
  }

  public function addHeader($name, $value) {
    $this->headers[$name][] = (string) $value;
  }

  public function setHeader($name, $value) {
    $this->headers[$name] = [(string) $value];
  }

  public function getHeaders() {
    return array_merge(
      $this->getRequestLineHeaders(),
      $this->getStandardHeaders(),
      $this->getCookieHeaders()
    );
  }

  public function setContent($content) {
    if(is_array($content) || is_object($content)) {
      $content = json_encode($content);
    }
    $this->content = (string) $content;
  }

  public function getContent() {
    return $this->content;
  }

  public function redirect($url) {

    if(strpos($url, 'http') === false) {
      $url = $this->baseUrl.trim($url, '/');
    }

    $url = filter_var($url, FILTER_SANITIZE_URL);

    $this->setStatusCode(302);
    $this->setHeader('Location', $url);

    // redirect
    $headers = $this->getHeaders();
    foreach($headers as $header) {
      header($header, false);
    }
    exit();
  }

  public function addCookie(Cookie $cookie) {
    $this->cookies[$cookie->getName()] = $cookie;
  }

  public function deleteCookie(Cookie $cookie) {
    $cookie->setValue('');
    $cookie->setMaxAge(-1);
    $this->cookies[$cookie->getName()] = $cookie;
  }

  private function getRequestLineHeaders() {
    $headers = [];
    $headers[] = "HTTP/{$this->version} {$this->statusCode} {$this->statusText}";

    switch($this->contentType) {
      case 'html':
      case 'text/html':
        $contentType = 'text/html';
        break;

      case 'json':
      case 'application/json':
        $contentType = 'application/json';
        break;

      default:
        $contentType = $this->contentType;
    }
    $headers[] = "Content-Type: {$contentType}; Charset = {$this->charset}";

    return $headers;
  }

  private function getStandardHeaders() {
    $headers = [];

    foreach($this->headers as $name => $values) {
      foreach ($values as $value) {
        $headers[] = "{$name}: {$value}";
      }
    }

    return $headers;
  }

  private function getCookieHeaders() {
    $headers = [];

    foreach($this->cookies as $cookie) {
      $headers[] = "Set-Cookie: ".$cookie->getHeaderString();
    }

    return $headers;
  }

}
