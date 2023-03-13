<?php

namespace SMVC\Controller;

use SMVC\Core\Controller;
use SMVC\Helpers\Text;
use SMVC\Helpers\Widgets;
use SMVC\Helpers\Utils;

class DashboardController extends Controller {

  public function __construct($app) {
    parent::__construct($app);

    // set partial to use as main
    $this->view->setPartial('main');

    // set page title (the <title> field)
    $this->view->setVar('pagetitle', 'Dashboard');

    // active menu
    $this->view->setVar('active', 'dashboard');
  }

  public function dashboard() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', 'Dashboard');

    // load dashboard widgets per group
    $widgets = [
      'default' => [
        'widgets' => [
          'clock' => Widgets::clock()
        ]
      ]
    ];
    $widgets = json_decode(json_encode($widgets));
    $this->view->setVar('dashboard_widgets', $widgets);

    // show dashboard page
    return $this->view->page('dashboard');
  }

}
