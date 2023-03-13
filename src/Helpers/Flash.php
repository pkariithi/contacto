<?php

namespace SMVC\Helpers;

class Flash {

  private $app;
  private $flash = null;

  public function __construct($app) {
    $this->app = $app;
  }

  public function set($message = [], $class = 'info', $redirect = false) {

    if(!is_array($message)) {
      $message = [$message];
    }

    $this->app->session->set('flash', [
      'message' => $message,
      'class' => $class
    ]);

    if($redirect) {
      $url = strtolower($redirect);
      $this->app->response->setStatusCode(302);
      $this->app->response->redirect($url);
    }
    return;
  }

  public function get() {
    if($this->app->session->has('flash')) {
      return json_decode(json_encode($this->app->session->pull('flash')));
    }
    return false;
  }

  public function saveFlash($html = null) {
    if(!empty($html)) {
      $this->flash = $html;
    }
  }

  public function displayFlash() {
    return $this->flash;
  }

}
