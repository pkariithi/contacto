<?php

namespace SMVC\Controller;

use SMVC\Core\Controller;
use SMVC\Model\Permission;

use SMVC\Helpers\Text;
use SMVC\Helpers\Pager;
use SMVC\Helpers\Pdf;

class PermissionController extends Controller {

  public function __construct($app) {
    parent::__construct($app);

    // set partial to use as main
    $this->view->setPartial('main');

    // set page title (the <title> field)
    $this->view->setVar('pagetitle', 'Permissions');

    // active menu
    $this->view->setVar('active', 'permissions');

    // permission model
    $this->model->permission = new Permission($app);

    // resource name
    $this->resource_name = 'permissions';
    $this->resource_type = 'permission';
    $this->view->setVar('resource_name', $this->resource_name);
  }

  public function listing() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if user has permission to the route
    if(!$this->rbac->hasPermission('Can view permissions')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // load options
    $options = $this->loadOptions($this->model->permission->listing);

    // reset
    if(isset($options['submit']) && $options['submit'] == 'reset') {
      $this->app->response->redirect('/permissions');
    }

    // fetch permissions
    $permissions = $this->model->permission->fetchPermissions([], $options);

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', 'Manage Permissions');

    // filter form
    $filter_form = $this->getResourceFilterform('permissions', $this->model->permission->listing, $options);
    $this->view->setVar('filter_form', $filter_form);

    // links
    $links = $this->getLinksMarkup($this->model->permission->links, $permissions->rows);
    foreach($links as $name => $markup) {
      if(!empty($markup)) {
        $this->view->setVar($name, $markup);
      }
    }

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'Permissions']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // pager
    $pager = Pager::paginate(
      $permissions->meta->count,
      $permissions->meta->page,
      $permissions->meta->pages,
      'permissions/',
      $this->app->request->getGetParamsAsUri()
    );
    $this->view->setVar('pager', $pager);

    // return form view
    $this->view->setVar('records', $permissions->rows);
    $this->view->setVar('columns', $this->model->permission->listing);
    return $this->view->page('resource.listing');
  }

  public function view() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if user has permission to the route
    if(!$this->rbac->hasPermission('Can view permissions')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get permission id param
    $permission_id = $this->app->route->values->permission_id ?? null;
    if(empty($permission_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch permission
    $permission = $this->model->permission->fetchPermission(['id' => $permission_id]);
    if(empty($permission)) {
      return $this->app->response->redirect('404');
    }

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', $permission->permission);

    // links
    $links = $this->getLinksMarkup($this->model->permission->links, [$permission]);
    foreach($links as $name => $markup) {
      if(!empty($markup)) {
        $this->view->setVar($name, $markup);
      }
    }

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'permissions','label'=>'Permissions','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>$permission->permission]);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // return detail view
    $this->view->setVar('resource', $permission);
    $this->view->setVar('columns', !empty($this->model->permission->detail) ? $this->model->permission->detail : $this->model->permission->listing);
    return $this->view->page('resource.detail');
  }

  public function export() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if permission has permission to the route
    if(!$this->rbac->hasPermission(['Can export permissions'])) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // fetch permissions
    $permissions = $this->model->permission->fetchPermissions([], ["perPage" => "all"]);

    // export
    $pdf = new Pdf();
    return $pdf->exportListing(
      'Permissions',
      $this->model->permission->listing,
      $permissions->rows
    );
  }

  public function enable() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if user has permission to the route
    if(!$this->rbac->hasPermission('Can enable permissions')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get permission id param
    $permission_id = $this->app->route->values->permission_id ?? null;
    if(empty($permission_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch permission
    $permission = $this->model->permission->fetchPermission(['id' => $permission_id]);
    if(empty($permission)) {
      return $this->app->response->redirect('404');
    }

    // check if permission is already enabled
    if($permission->status == 'Active') {
      $this->app->flash->set("Permission '{$permission->permission}' is already enabled", 'error', '/permissions/'.$permission->id.'/view');
    }

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', "Enable Permission '{$permission->permission}'");

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'permissions','label'=>'Permissions','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'permissions/'.$permission->id.'/view','label'=>$permission->permission,'type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'Enable']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // form
    $form = $this->getEnableForm($this->model->permission->form, 'permissions/'.$permission->id.'/enable');
    $this->view->setVar('form', $form);
    $this->view->setVar('resource_type', $this->resource_type);

    // return form view
    $this->view->setVar('resource', $permission);
    $this->view->setVar('columns', $this->model->permission->listing);
    return $this->view->page('resource.enable');
  }

  public function processEnable() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if user has permission to the route
    if(!$this->rbac->hasPermission('Can enable permissions')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get permission id param
    $permission_id = $this->app->route->values->permission_id ?? null;
    if(empty($permission_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch permission
    $permission = $this->model->permission->fetchPermission(['id' => $permission_id]);
    if(empty($permission)) {
      return $this->app->response->redirect('404');
    }

    // check if permission is already enabled
    if($permission->status == 'Active') {
      $this->app->flash->set("Permission '{$permission->permission}' is already enabled", 'error', '/permissions/'.$permission->id.'/view');
    }

    // get post data
    $post = $this->app->request->getPostParams();
    $post = (object) $this->expectedOnly(['submit'], $post);

    // disable or cancel
    if(isset($post->submit) && $post->submit == 'enable'):

      // disable
      $disable = $this->model->permission->enablePermission(['id' => $permission->id]);
      if($disable === false) {
        $this->app->flash->set("Server error while activating permission '{$permission->permission}'", 'error', "/permissions/{$permission->id}/view");
        return;
      }

      // redirect to permission
      $this->app->flash->set("Permission '{$permission->permission}' enabled successfully", 'success', "/permissions/{$permission->id}/view");
      return;

    else:

      // redirect to permission
      $this->app->response->redirect("/permissions/{$permission->id}/view");
      return $this->app->response;

    endif;
  }

  public function disable() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if user has permission to the route
    if(!$this->rbac->hasPermission('Can disable permissions')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get permission id param
    $permission_id = $this->app->route->values->permission_id ?? null;
    if(empty($permission_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch permission
    $permission = $this->model->permission->fetchPermission(['id' => $permission_id]);
    if(empty($permission)) {
      return $this->app->response->redirect('404');
    }

    // check if permission is already disabled
    if($permission->status == 'Inactive') {
      $this->app->flash->set("Permission '{$permission->permission}' is already disabled", 'error', '/permissions/'.$permission->id.'/view');
    }

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', "Disable Permission '{$permission->permission}'");

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'permissions','label'=>'Permissions','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'permissions/'.$permission->id.'/view','label'=>$permission->permission,'type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'Disable']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // form
    $form = $this->getDisableForm($this->model->permission->form, 'permissions/'.$permission->id.'/disable');
    $this->view->setVar('form', $form);
    $this->view->setVar('resource_type', $this->resource_type);

    // return form view
    $this->view->setVar('resource', $permission);
    $this->view->setVar('columns', $this->model->permission->listing);
    return $this->view->page('resource.disable');
  }

  public function processDisable() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if user has permission to the route
    if(!$this->rbac->hasPermission('Can disable permissions')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get permission id param
    $permission_id = $this->app->route->values->permission_id ?? null;
    if(empty($permission_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch permission
    $permission = $this->model->permission->fetchPermission(['id' => $permission_id]);
    if(empty($permission)) {
      return $this->app->response->redirect('404');
    }

    // check if permission is already disabled
    if($permission->status == 'Inactive') {
      $this->app->flash->set("Permission '{$permission->permission}' is already disabled", 'error', '/permissions/'.$permission->id.'/view');
    }

    // get post data
    $post = $this->app->request->getPostParams();
    $post = (object) $this->expectedOnly(['submit'], $post);

    // disable or cancel
    if(isset($post->submit) && $post->submit == 'disable'):

      // disable
      $disable = $this->model->permission->disablePermission(['id' => $permission->id]);
      if($disable === false) {
        $this->app->flash->set("Server error while deactivating permission '{$permission->permission}'", 'error', "/permissions/{$permission->id}/view");
        return;
      }

      // redirect to permission
      $this->app->flash->set("Permission '{$permission->permission}' disabled successfully", 'success', "/permissions/{$permission->id}/view");
      return;

    else:

      // redirect to permission
      $this->app->response->redirect("/permissions/{$permission->id}/view");
      return $this->app->response;

    endif;
  }

}
