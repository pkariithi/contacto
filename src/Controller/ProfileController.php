<?php

namespace SMVC\Controller;

use SMVC\Core\Controller;

class ProfileController extends Controller {

  public function __construct($app) {
    parent::__construct($app);

    // set partial to use as main
    $this->view->setPartial('main');

    // set page title (the <title> field)
    $this->view->setVar('pagetitle', 'Profile');

    // active menu
    $this->view->setVar('active', 'profile');
  }

  public function profile() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', 'Profile');

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'Profile']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // show profile page
    return $this->view->page('profile');
  }

}
