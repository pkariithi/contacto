<?php

namespace SMVC\Controller;

use SMVC\Core\Controller;

class ErrorController extends Controller {

  public function __construct($app) {
    parent::__construct($app);

    $partial = $this->is_logged_in ? 'main' : 'error';
    $this->view->setPartial($partial);

    // active menu
    $this->view->setVar('active', '');
  }

  public function e401() {
    $this->app->log->info(['TransactionType'=>'ERROR','Action'=>'401','File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    $error_page = [
      'title' => '401 Error - Unauthorized',
      'body' => 'You do not have permissions to access this resource.<br />Kindly request for access from your manager.',
      'show_login_link' => true
    ];
    $this->view->setVar('error_page', (object) $error_page);

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', $error_page['title']);

    // show dashboard page
    return $this->view->page('error');
  }

  public function e404() {
    $this->app->log->info(['TransactionType'=>'ERROR','Action'=>'404','File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    $error_page = [
      'title' => '404 Error - Page not Found',
      'body' => 'The page you are looking for cannot be found',
      'show_login_link' => true
    ];
    $this->view->setVar('error_page', (object) $error_page);

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', $error_page['title']);

    // show dashboard page
    return $this->view->page('error');
  }

  public function e500() {
     $this->app->log->info(['TransactionType'=>'ERROR','Action'=>'500','File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    $error_page = [
      'title' => '500 Error - Internal Server Error',
      'body' => 'An internal server error has occured. We have noted it and will be fixing it soon.',
      'show_login_link' => true
    ];
    $this->view->setVar('error_page', (object) $error_page);

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', $error_page['title']);

    // show dashboard page
    return $this->view->page('error');
  }

}
