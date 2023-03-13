<?php

namespace SMVC\Controller;

use SMVC\Core\Controller;
use SMVC\Model\Session;

use SMVC\Helpers\Pdf;
use SMVC\Helpers\Pager;

class SessionController extends Controller {

  public function __construct($app) {
    parent::__construct($app);

    // set partial to use as main
    $this->view->setPartial('main');

    // set page title (the <title> field)
    $this->view->setVar('pagetitle', 'Sessions');

    // active menu
    $this->view->setVar('active', 'sessions');

    // session model
    $this->model->session = new Session($app);

    // resource name
    $this->resource_name = 'sessions';
    $this->resource_type = 'session';
    $this->view->setVar('resource_name', $this->resource_name);
  }

  public function listing() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if user has session to the route
    if(!$this->rbac->hasPermission('Can view sessions')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Session denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // load options
    $options = $this->loadOptions($this->model->session->listing);

    // reset
    if(isset($options['submit']) && $options['submit'] == 'reset') {
      $this->app->response->redirect('/sessions');
    }

    // fetch sessions
    $sessions = $this->model->session->fetchSessions([], $options);

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', 'Manage Sessions');

    // filter form
    $filter_form = $this->getResourceFilterform('sessions', $this->model->session->listing, $options);
    $this->view->setVar('filter_form', $filter_form);

    // links
    $links = $this->getLinksMarkup($this->model->session->links, $sessions->rows);
    foreach($links as $name => $markup) {
      if(!empty($markup)) {
        $this->view->setVar($name, $markup);
      }
    }

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'Sessions']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // pager
    $pager = Pager::paginate(
      $sessions->meta->count,
      $sessions->meta->page,
      $sessions->meta->pages,
      'sessions/',
      $this->app->request->getGetParamsAsUri()
    );
    $this->view->setVar('pager', $pager);

    // return form view
    $this->view->setVar('records', $sessions->rows);
    $this->view->setVar('columns', $this->model->session->listing);
    return $this->view->page('resource.listing');
  }

  public function view() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if user has session to the route
    if(!$this->rbac->hasPermission('Can view sessions')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Session denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get session id param
    $session_id = $this->app->route->values->session_id ?? null;
    if(empty($session_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch session
    $session = $this->model->session->fetchSession(['id' => $session_id]);
    if(empty($session)) {
      return $this->app->response->redirect('404');
    }

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', $session->username);

    // links
    $links = $this->getLinksMarkup($this->model->session->links, [$session]);
    foreach($links as $name => $markup) {
      if(!empty($markup)) {
        $this->view->setVar($name, $markup);
      }
    }

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'sessions','label'=>'Sessions','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>$session->username]);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // return detail view
    $this->view->setVar('resource', $session);
    $this->view->setVar('columns', !empty($this->model->session->detail) ? $this->model->session->detail : $this->model->session->listing);
    return $this->view->page('resource.detail');
  }

  public function export() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if session has permission to the route
    if(!$this->rbac->hasPermission(['Can export sessions'])) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // fetch sessions
    $sessions = $this->model->session->fetchSessions([], ["perPage" => "all"]);

    // export
    $pdf = new Pdf();
    return $pdf->exportListing(
      'Sessions',
      $this->model->session->listing,
      $sessions->rows
    );
  }

}
