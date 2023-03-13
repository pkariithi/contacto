<?php

namespace SMVC\Controller;

use SMVC\Core\Controller;
use SMVC\Model\Messaging;
use SMVC\Model\Contact;

use SMVC\Helpers\Pdf;
use SMVC\Helpers\Pager;
use SMVC\Helpers\File;
use SMVC\Helpers\Text;

class MessagingController extends Controller {

  public function __construct($app) {
    parent::__construct($app);

    // set partial to use as main
    $this->view->setPartial('main');

    // set base page title
    $this->basePageTitle = 'Messaging';

    // active menu
    $this->view->setVar('active', 'messaging');

    // messaging model
    $this->model->messaging = new Messaging($app);
    $this->view->setVar('module', $this->model->messaging->module);

    // resource name
    $this->resource_name = 'messaging';
    $this->resource_type = 'messaging';
    $this->view->setVar('resource_name', $this->resource_name);
  }

  public function listing() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if messaging has permission to the route
    if(!$this->rbac->hasPermission('Can view sent messages')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // load options
    $options = $this->loadOptions($this->model->messaging->listing);

    // reset
    if(isset($options['submit']) && $options['submit'] == 'reset') {
      $this->app->response->redirect('/messaging');
    }

    // fetch messaging
    $messaging = $this->model->messaging->fetchMessagings([], $options);

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', 'Messagings');

    // filter form
    $filter_form = $this->getResourceFilterform('messaging', $this->model->messaging->listing, $options);
    $this->view->setVar('filter_form', $filter_form);

    // links
    $links = $this->getLinksMarkup($this->model->messaging->links, $messaging->rows);
    foreach($links as $name => $markup) {
      if(!empty($markup)) {
        $this->view->setVar($name, $markup);
      }
    }

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'Messagings']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // pager
    $pager = Pager::paginate(
      $messaging->meta->count,
      $messaging->meta->page,
      $messaging->meta->pages,
      'messaging/',
      $this->app->request->getGetParamsAsUri()
    );
    $this->view->setVar('pager', $pager);

    // set page title
    $this->view->setVar('pagetitle', [$this->basePageTitle]);

    // return form view
    $this->view->setVar('records', $messaging->rows);
    $this->view->setVar('columns', $this->model->messaging->listing);
    return $this->view->page('resource.listing');
  }

  public function view() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if messaging has permission to the route
    if(!$this->rbac->hasPermission('Can view sent messages')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get messaging id param
    $messaging_id = $this->app->route->values->messaging_id ?? null;
    if(empty($messaging_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch messaging
    $messaging = $this->model->messaging->fetchMessaging(['id' => $messaging_id]);
    if(!isset($messaging->id)) {
      return $this->app->response->redirect('404');
    }

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', Text::excerpt($messaging->message, 40));

    // links
    $links = $this->getLinksMarkup($this->model->messaging->links, [$messaging]);
    foreach($links as $name => $markup) {
      if(!empty($markup)) {
        $this->view->setVar($name, $markup);
      }
    }

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'messaging','label'=>'Messagings','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>Text::excerpt($messaging->message, 40)]);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // set page title
    $this->view->setVar('pagetitle', [Text::excerpt($messaging->message, 40), $this->basePageTitle]);

    // return detail view
    $this->view->setVar('resource', $messaging);
    $this->view->setVar('columns', !empty($this->model->messaging->detail) ? $this->model->messaging->detail : $this->model->messaging->listing);
    return $this->view->page('resource.detail');
  }

  public function export() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if messaging has permission to the route
    if(!$this->rbac->hasPermission(['Can export sent messages'])) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // fetch messaging
    $messaging = $this->model->messaging->fetchMessagings([], ["perPage" => "all"]);

    // export
    $pdf = new Pdf();
    return $pdf->exportListing(
      'Messaging',
      $this->model->messaging->listing,
      $messaging->rows
    );
  }

}
