<?php

namespace SMVC\Controller;

use SMVC\Core\Controller;

use SMVC\Model\Role;
use SMVC\Model\Permission;
use SMVC\Model\User;
use SMVC\Model\Status;

use SMVC\Helpers\Pager;
use SMVC\Helpers\Pdf;

class RoleController extends Controller {

  public function __construct($app) {
    parent::__construct($app);

    // set partial to use as main
    $this->view->setPartial('main');

    // set page title (the <title> field)
    $this->view->setVar('pagetitle', 'Roles');

    // active menu
    $this->view->setVar('active', 'roles');

    // role model
    $this->model->role = new Role($app);

    // resource name
    $this->resource_name = 'roles';
    $this->resource_type = 'role';
    $this->view->setVar('resource_name', $this->resource_name);
  }

  public function listing() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if user has permission to the route
    if(!$this->rbac->hasPermission('Can view roles')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // load options
    $options = $this->loadOptions($this->model->role->listing);

    // reset
    if(isset($options['submit']) && $options['submit'] == 'reset') {
      $this->app->response->redirect('/roles');
    }

    // fetch roles
    $roles = $this->model->role->fetchRoles([], $options);

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', 'Manage Roles');

    // filter form
    $filter_form = $this->getResourceFilterform('roles', $this->model->role->listing, $options);
    $this->view->setVar('filter_form', $filter_form);

    // links
    $links = $this->getLinksMarkup($this->model->role->links, $roles->rows);
    foreach($links as $name => $markup) {
      if(!empty($markup)) {
        $this->view->setVar($name, $markup);
      }
    }

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'Roles']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // pager
    $pager = Pager::paginate(
      $roles->meta->count,
      $roles->meta->page,
      $roles->meta->pages,
      'roles/',
      $this->app->request->getGetParamsAsUri()
    );
    $this->view->setVar('pager', $pager);

    // return form view
    $this->view->setVar('records', $roles->rows);
    $this->view->setVar('columns', $this->model->role->listing);
    return $this->view->page('resource.listing');
  }

  public function view() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if user has permission to the route
    if(!$this->rbac->hasPermission('Can view roles')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get role id param
    $role_id = $this->app->route->values->role_id ?? null;
    if(empty($role_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch role
    $role = $this->model->role->fetchRole(['id' => $role_id]);
    if(empty($role)) {
      return $this->app->response->redirect('404');
    }

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', $role->role);

    // links
    $links = $this->getLinksMarkup($this->model->role->links, [$role]);
    foreach($links as $name => $markup) {
      if(!empty($markup)) {
        $this->view->setVar($name, $markup);
      }
    }

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'roles','label'=>'Roles','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>$role->role]);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // return detail view
    $this->view->setVar('resource', $role);
    $this->view->setVar('columns', !empty($this->model->role->detail) ? $this->model->role->detail : $this->model->role->listing);
    return $this->view->page('resource.detail');
  }

  public function export() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if role has permission to the route
    if(!$this->rbac->hasPermission(['Can export roles'])) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // fetch roles
    $roles = $this->model->role->fetchRoles([], ["perPage" => "all"]);

    // export
    $pdf = new Pdf();
    return $pdf->exportListing(
      'Roles',
      $this->model->role->listing,
      $roles->rows
    );
  }

  public function new() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if user has permission to the route
    if(!$this->rbac->hasPermission('Can add roles')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', 'New Role');

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'roles','label'=>'Roles','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'New Role']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // form
    $form = $this->getResourceForm('new','roles/new', $this->model->role->form);
    $this->view->setVar('form', $form);

    // return form view
    return $this->view->page('resource.new');
  }

  public function processNew() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if user has permission to the route
    if(!$this->rbac->hasPermission('Can add roles')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get and validate post data
    $validate = $this->validateResourcePostData($this->model->role->form);
    if(!$validate->success) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Validation failed. Errors are {".json_encode($validate->errors)."}"]);
      $this->app->flash->set($validate->errors, 'error', '/roles/new');
      return;
    }

    // data
    $data = (object) $validate->data;

    // delete session post data and cancel edition
    if($data->submit != 'new') {
      $this->deleteResourceSessionPostData();
      return $this->app->response->redirect("/roles");
    }

    // check if role exists
    $role = $this->model->role->fetchRole(['role'=>$data->role]);
    if($role) {
      $this->app->flash->set("Role '{$data->role}' already exists", 'error', '/roles/new');
    }

    // save in database
    $insert = $this->model->role->newRole($data->role, $data->description, $this->loggedInUser->id);
    if(!$insert) {
      $this->app->flash->set('Server error while creating role', 'error', '/roles');
      return;
    }

    // delete resource session post data
    $this->deleteResourceSessionPostData();

    // redirect to created role
    $this->app->flash->set("Role '{$data->role}' created successfully", 'success', '/roles/'.$insert.'/view');
    return;
  }

  public function edit() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if user has permission to the route
    if(!$this->rbac->hasPermission('Can edit roles')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get role id param
    $role_id = $this->app->route->values->role_id ?? null;
    if(empty($role_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch role
    $role = $this->model->role->fetchRole(['id' => $role_id]);
    if(empty($role)) {
      return $this->app->response->redirect('404');
    }

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', "Edit Role '{$role->role}'");

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'roles','label'=>'Roles','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'roles/'.$role->id.'/view','label'=>$role->role,'type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'Edit']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // form
    $form = $this->getResourceForm('edit', 'roles/'.$role->id.'/edit', $this->model->role->form, $role);
    $this->view->setVar('form', $form);

    // return form view
    return $this->view->page('resource.edit');
  }

  public function processEdit() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if user has permission to the route
    if(!$this->rbac->hasPermission('Can edit roles')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get role id param
    $role_id = $this->app->route->values->role_id ?? null;
    if(empty($role_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch role
    $role = $this->model->role->fetchRole(['id' => $role_id]);
    if(empty($role)) {
      return $this->app->response->redirect('404');
    }

    // get and validate post data
    $validate = $this->validateResourcePostData($this->model->role->form);
    if(!$validate->success) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Validation failed. Errors are {".json_encode($validate->errors)."}"]);
      $this->app->flash->set($validate->errors, 'error', '/roles/'.$role->id.'/edit');
      return;
    }

    // data
    $data = (object) $validate->data;

    // check if role with new name already exists
    if($role->role != $data->role) {
      $r = $this->model->role->fetchRole(['role'=>$data->role]);
      if($r) {
        $this->app->flash->set("Role '{$data->role}' already exists", 'error', "/roles/{$role->id}/edit");
      }
    }

    // delete session post data and cancel edition
    if($data->submit != 'edit') {
      $this->deleteResourceSessionPostData();
      return $this->app->response->redirect("/roles/{$role->id}/view");
    }

    // save in database
    unset($data->submit);
    $update = $this->model->role->updateRole($data, ['id' => $role->id]);
    if($update === false) {
      $this->app->flash->set("Server error while editing role '{$role->role}'", 'error', "/roles/{$role->id}/view");
      return;
    }

    // delete resource session post data
    $this->deleteResourceSessionPostData();

    // redirect to created role
    $this->app->flash->set("Role '{$data->role}' updated successfully", 'success', '/roles/'.$role->id.'/view');
    return;
  }

  public function delete() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if user has permission to the route
    if(!$this->rbac->hasPermission('Can delete roles')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get role id param
    $role_id = $this->app->route->values->role_id ?? null;
    if(empty($role_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch role
    $role = $this->model->role->fetchRole(['id' => $role_id]);
    if(empty($role)) {
      return $this->app->response->redirect('404');
    }

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', "Delete Role '{$role->role}'");

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'roles','label'=>'Roles','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'roles/'.$role->id.'/view','label'=>$role->role,'type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'Delete']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // form
    $form = $this->getDeleteForm($this->model->role->form, 'roles/'.$role->id.'/delete');
    $this->view->setVar('form', $form);
    $this->view->setVar('resource_type', $this->resource_type);

    // return form view
    $this->view->setVar('resource', $role);
    $this->view->setVar('columns', $this->model->role->listing);
    return $this->view->page('resource.delete');
  }

  public function processDelete() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if user has permission to the route
    if(!$this->rbac->hasPermission('Can delete roles')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get role id param
    $role_id = $this->app->route->values->role_id ?? null;
    if(empty($role_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch role
    $role = $this->model->role->fetchRole(['id' => $role_id]);
    if(empty($role)) {
      return $this->app->response->redirect('404');
    }

    // get post data
    $post = $this->app->request->getPostParams();
    $post = (object) $this->expectedOnly(['submit'], $post);

    // cancel form
    if(isset($post->submit) && $post->submit != 'delete') {
      $this->app->response->redirect("/roles/{$role->id}/view");
      return $this->app->response;
    }

    // process delete
    $delete_role = $this->model->role->deleteRole(['id' => $role->id],['limit' => 1]);
    if($delete_role === false) {
      $this->app->flash->set("Server error while deleting role '{$role->role}'", 'error', "/roles/{$role->id}/view");
      return;
    }

    // delete role permissions
    $this->model->role->deleteRolePermissions($role->id);

    // redirect to created role
    $this->app->flash->set("Role '{$role->role}' deleted successfully", 'success', '/roles');
    return;
  }

  public function enable() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if user has permission to the route
    if(!$this->rbac->hasPermission('Can enable roles')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get role id param
    $role_id = $this->app->route->values->role_id ?? null;
    if(empty($role_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch role
    $role = $this->model->role->fetchRole(['id' => $role_id]);
    if(empty($role)) {
      return $this->app->response->redirect('404');
    }

    // check if role is already enabled
    if($role->status == 'Active') {
      $this->app->flash->set("Role '{$role->role}' is already enabled", 'error', '/roles/'.$role->id.'/view');
    }

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', "Enable Role '{$role->role}'");

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'roles','label'=>'Roles','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'roles/'.$role->id.'/view','label'=>$role->role,'type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'Enable']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // form
    $form = $this->getEnableForm($this->model->role->form, 'roles/'.$role->id.'/enable');
    $this->view->setVar('form', $form);
    $this->view->setVar('resource_type', $this->resource_type);

    // return form view
    $this->view->setVar('resource', $role);
    $this->view->setVar('columns', $this->model->role->listing);
    return $this->view->page('resource.enable');
  }

  public function processEnable() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if user has permission to the route
    if(!$this->rbac->hasPermission('Can enable roles')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get role id param
    $role_id = $this->app->route->values->role_id ?? null;
    if(empty($role_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch role
    $role = $this->model->role->fetchRole(['id' => $role_id]);
    if(empty($role)) {
      return $this->app->response->redirect('404');
    }

    // check if role is already enabled
    if($role->status == 'Active') {
      $this->app->flash->set("Role '{$role->role}' is already enabled", 'error', '/roles/'.$role->id.'/view');
    }

    // get post data
    $post = $this->app->request->getPostParams();
    $post = (object) $this->expectedOnly(['submit'], $post);

    // cancel form
    if(isset($post->submit) && $post->submit != 'enable') {
      $this->app->response->redirect("/roles/{$role->id}/view");
      return $this->app->response;
    }

    // enable
    $enable = $this->model->role->enableRole(['id' => $role->id]);
    if($enable === false) {
      $this->app->flash->set("Server error while activating role '{$role->role}'", 'error', "/roles/{$role->id}/view");
      return;
    }

    // redirect to role
    $this->app->flash->set("Role '{$role->role}' enabled successfully", 'success', "/roles/{$role->id}/view");
    return;
  }

  public function disable() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if user has permission to the route
    if(!$this->rbac->hasPermission('Can disable roles')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get role id param
    $role_id = $this->app->route->values->role_id ?? null;
    if(empty($role_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch role
    $role = $this->model->role->fetchRole(['id' => $role_id]);
    if(empty($role)) {
      return $this->app->response->redirect('404');
    }

    // check if role is already disabled
    if($role->status == 'Inactive') {
      $this->app->flash->set("Role '{$role->role}' is already disabled", 'error', '/roles/'.$role->id.'/view');
    }

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', "Disable Role '{$role->role}'");

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'roles','label'=>'Roles','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'roles/'.$role->id.'/view','label'=>$role->role,'type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'Disable']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // form
    $form = $this->getDisableForm($this->model->role->form, 'roles/'.$role->id.'/disable');
    $this->view->setVar('form', $form);
    $this->view->setVar('resource_type', $this->resource_type);

    // return form view
    $this->view->setVar('resource', $role);
    $this->view->setVar('columns', $this->model->role->listing);
    return $this->view->page('resource.disable');
  }

  public function processDisable() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if user has permission to the route
    if(!$this->rbac->hasPermission('Can disable roles')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get role id param
    $role_id = $this->app->route->values->role_id ?? null;
    if(empty($role_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch role
    $role = $this->model->role->fetchRole(['id' => $role_id]);
    if(empty($role)) {
      return $this->app->response->redirect('404');
    }

    // check if role is already disabled
    if($role->status == 'Inactive') {
      $this->app->flash->set("Role '{$role->role}' is already disabled", 'error', '/roles/'.$role->id.'/view');
    }

    // get post data
    $post = $this->app->request->getPostParams();
    $post = (object) $this->expectedOnly(['submit'], $post);

    // cancel form
    if(isset($post->submit) && $post->submit != 'disable') {
      $this->app->response->redirect("/roles/{$role->id}/view");
      return $this->app->response;
    }

    // process disable
    $disable = $this->model->role->disableRole(['id' => $role->id]);
    if($disable === false) {
      $this->app->flash->set("Server error while deactivating role '{$role->role}'", 'error', "/roles/{$role->id}/view");
      return;
    }

    // redirect to role
    $this->app->flash->set("Role '{$role->role}' disabled successfully", 'success', "/roles/{$role->id}/view");
    return;
  }

  public function permissions() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if user has permission to the route
    if(!$this->rbac->hasPermission(['Can manage role permissions'])) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get role id param
    $role_id = $this->app->route->values->role_id ?? null;
    if(empty($role_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch role
    $role = $this->model->role->fetchRole(['id' => $role_id]);
    if(empty($role)) {
      return $this->app->response->redirect('404');
    }

    // check if role is already disabled
    if($role->status == 'Inactive') {
      $this->app->flash->set("Role '{$role->role}' is disabled", 'error', '/roles/'.$role->id.'/view');
    }

    // fetch role permissions
    $this->model->permission = new Permission($this->app);
    $role_permissions = $this->model->permission->loadRolePermissions($role->id);
    $role->permissions = [];
    foreach($role_permissions as $rp) {
      $role->permissions[] = $rp->id;
    }
    $this->view->setVar('role', $role);

    // fetch all permissions
    $permissions = $this->model->permission->fetchPermissions([], ['perPage' => 'all']);
    $formatted_permissions = $formatted_modules = [];
    foreach($permissions->rows as $p) {
      $formatted_permissions[$p->module][$p->id] = $p;
    }
    foreach($formatted_permissions as $fp_module => $fp) {
      $formatted_modules[$fp_module] = array_keys($fp);
    }
    $this->view->setVar('permissions', json_decode(json_encode($formatted_permissions)));

    // checked permission modules
    $checked_modules = [];
    foreach($formatted_modules as $fm_name => $fm_keys) {
      $checked = array_intersect($fm_keys, $role->permissions);
      if(count($checked) == count($fm_keys)) {
        $checked_modules[] = $fm_name;
      }
    }
    $this->view->setVar('checked_modules', $checked_modules);

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', "Manage Permissions for Role '{$role->role}'");

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'roles','label'=>'Roles','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'roles/'.$role->id.'/view','label'=>$role->role,'type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'Manage Permissions']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // view
    return $this->view->page('roles.permissions');
  }

  public function processPermissions() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if user has permission to the route
    if(!$this->rbac->hasPermission(['Can manage role permissions'])) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get role id param
    $role_id = $this->app->route->values->role_id ?? null;
    if(empty($role_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch role
    $role = $this->model->role->fetchRole(['id' => $role_id]);
    if(empty($role)) {
      return $this->app->response->redirect('404');
    }

    // check if role is already disabled
    if($role->status == 'Inactive') {
      $this->app->flash->set("Role '{$role->role}' is disabled", 'error', '/roles/'.$role->id.'/view');
    }

    // fetch role permissions
    $this->model->permission = new Permission($this->app);
    $permissions = $this->model->permission->loadRolePermissions($role->id);
    $permission_ids = [];
    foreach($permissions as $p) {
      $permission_ids[] = $p->id;
    }

    // post data
    $post = $this->app->request->getPostParams();
    $post = (object) $this->expectedOnly(['permission'], $post);

    // save permissions
    $this->model->permission = new Permission($this->app);
    $this->model->permission->savePermissions($role->id, $this->loggedInUser->id, $post->permission, array_diff($post->permission, $permission_ids));

    // redirect to role
    $this->app->flash->set("Permissions for Role '{$role->role}' updated successfully", 'success', '/roles/'.$role->id.'/permissions');
    return;
  }

  public function users() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if user has permission to the route
    if(!$this->rbac->hasPermission(['Can manage role users'])) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get role id param
    $role_id = $this->app->route->values->role_id ?? null;
    if(empty($role_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch role
    $role = $this->model->role->fetchRole(['id' => $role_id]);
    if(empty($role)) {
      return $this->app->response->redirect('404');
    }

    // check if role is already disabled
    if($role->status == 'Inactive') {
      $this->app->flash->set("Role '{$role->role}' is disabled", 'error', '/roles/'.$role->id.'/view');
    }

    // fetch role users
    $this->model->user = new User($this->app);
    $role_users = $this->model->user->loadRoleUsers($role->id);
    $role->users = [];
    foreach($role_users as $ra) {
      $role->users[] = $ra->id;
    }
    $this->view->setVar('role', $role);

    // fetch all users
    $users = $this->model->user->fetchUsers(
      ['status_id' => Status::ACTIVE_STATUS],
      ['perPage' => 'all']
    );
    $formatted_users = [];
    foreach($users->rows as $a) {
      $formatted_users[$a->id] = $a;
    }
    $this->view->setVar('users', json_decode(json_encode($formatted_users)));

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', "Manage Users for Role '{$role->role}'");

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'roles','label'=>'Roles','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'roles/'.$role->id.'/view','label'=>$role->role,'type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'Manage Users']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // view
    return $this->view->page('roles.users');
  }

  public function processUsers() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if user has permission to the route
    if(!$this->rbac->hasPermission(['Can manage role users'])) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get role id param
    $role_id = $this->app->route->values->role_id ?? null;
    if(empty($role_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch role
    $role = $this->model->role->fetchRole(['id' => $role_id]);
    if(empty($role)) {
      return $this->app->response->redirect('404');
    }

    // check if role is already disabled
    if($role->status == 'Inactive') {
      $this->app->flash->set("Role '{$role->role}' is disabled", 'error', '/roles/'.$role->id.'/view');
    }

    // fetch role users
    $this->model->user = new User($this->app);
    $role_users = $this->model->user->loadRoleUsers($role->id);
    $role_user_ids = [];
    foreach($role_users as $ra) {
      $role_user_ids[] = $ra->id;
    }

    // post data
    $post = $this->app->request->getPostParams();
    $post = (object) $this->expectedOnly(['user'], $post);

    // save users
    $this->model->user = new User($this->app);
    $this->model->user->saveRoleUsers($role->id, $this->loggedInUser->id, $post->user, array_diff($post->user, $role_user_ids));

    // redirect to role
    $this->app->flash->set("Users for Role '{$role->role}' updated successfully", 'success', '/roles/'.$role->id.'/users');
    return;
  }

}
