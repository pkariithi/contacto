<?php

namespace SMVC\Controller;

use SMVC\Core\Controller;
use SMVC\Model\User;
use SMVC\Model\Permission;
use SMVC\Model\Role;
use SMVC\Model\Forgot;

use SMVC\Helpers\Text;
use SMVC\Helpers\Pager;
use SMVC\Helpers\Pdf;

class UserController extends Controller {

  public function __construct($app) {
    parent::__construct($app);

    // set partial to use as main
    $this->view->setPartial('main');

    // set base page title
    $this->basePageTitle = 'Users';

    // active menu
    $this->view->setVar('active', 'users');

    // user model
    $this->model->user = new User($app);
    $this->view->setVar('module', $this->model->user->module);

    // resource name
    $this->resource_name = 'users';
    $this->resource_type = 'user';
    $this->view->setVar('resource_name', $this->resource_name);
  }

  public function listing() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if user has permission to the route
    if(!$this->rbac->hasPermission('Can view users')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // load options
    $options = $this->loadOptions($this->model->user->listing);

    // reset
    if(isset($options['submit']) && $options['submit'] == 'reset') {
      $this->app->response->redirect('/users');
    }

    // fetch users
    $users = $this->model->user->fetchUsers([], $options);

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', 'Manage Users');

    // filter form
    $filter_form = $this->getResourceFilterform('users', $this->model->user->listing, $options);
    $this->view->setVar('filter_form', $filter_form);

    // links
    $links = $this->getLinksMarkup($this->model->user->links, $users->rows);
    foreach($links as $name => $markup) {
      if(!empty($markup)) {
        $this->view->setVar($name, $markup);
      }
    }

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'Users']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // pager
    $pager = Pager::paginate(
      $users->meta->count,
      $users->meta->page,
      $users->meta->pages,
      'users/',
      $this->app->request->getGetParamsAsUri()
    );
    $this->view->setVar('pager', $pager);

    // set page title
    $this->view->setVar('pagetitle', [$this->basePageTitle]);

    // return form view
    $this->view->setVar('records', $users->rows);
    $this->view->setVar('columns', $this->model->user->listing);
    return $this->view->page('resource.listing');
  }

  public function view() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if user has permission to the route
    if(!$this->rbac->hasPermission('Can view users')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get user id param
    $user_id = $this->app->route->values->user_id ?? null;
    if(empty($user_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch user
    $user = $this->model->user->fetchUser(['id' => $user_id]);
    if(!isset($user->id)) {
      return $this->app->response->redirect('404');
    }

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', $user->username);

    // links
    $links = $this->getLinksMarkup($this->model->user->links, [$user]);
    foreach($links as $name => $markup) {
      if(!empty($markup)) {
        $this->view->setVar($name, $markup);
      }
    }

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'users','label'=>'Users','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>$user->username]);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // set page title
    $this->view->setVar('pagetitle', [$user->username, $this->basePageTitle]);

    // return detail view
    $this->view->setVar('resource', $user);
    $this->view->setVar('columns', !empty($this->model->user->detail) ? $this->model->user->detail : $this->model->user->listing);
    return $this->view->page('resource.detail');
  }

  public function export() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if user has permission to the route
    if(!$this->rbac->hasPermission(['Can export users'])) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // fetch users
    $users = $this->model->user->fetchUsers([], ["perPage" => "all"]);

    // export
    $pdf = new Pdf();
    return $pdf->exportListing(
      'Users',
      $this->model->user->listing,
      $users->rows
    );
  }

  public function new() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if user has permission to the route
    if(!$this->rbac->hasPermission('Can add users')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', 'New User');

    // set page title
    $this->view->setVar('pagetitle', ['New User', $this->basePageTitle]);

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'users','label'=>'Users','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'New User']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // build user model form
    $this->buildUserModelForm();

    // form
    $form = $this->getResourceForm('new','users/new', $this->model->user->form);
    $this->view->setVar('form', $form);

    // return form view
    return $this->view->page('resource.new');
  }

  public function processNew() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if user has permission to the route
    if(!$this->rbac->hasPermission('Can add users')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // build user model form
    $this->buildUserModelForm();

    // registered usernames and emails
    $registered = $this->model->user->getRegisteredUsernamesAndEmails();
    $this->model->user->form['resource']['username']['validate']['rules']['notin']['values'] = $registered['usernames'];
    $this->model->user->form['resource']['email']['validate']['rules']['notin']['values'] = $registered['emails'];

    // get and validate post data
    $validate = $this->validateResourcePostData($this->model->user->form);
    if(!$validate->success) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Validation failed. Errors are {".json_encode($validate->errors)."}"]);
      $this->app->flash->set($validate->errors, 'error', '/users/new');
      return;
    }

    // data
    $data = (object) $validate->data;

    // delete session post data and cancel edition
    if($data->submit != 'new') {
      $this->deleteResourceSessionPostData();
      return $this->app->response->redirect("/users");
    }

    // get roles ids
    $role_ids = [];
    if(isset($data->role) && !empty($data->role)) {

      // save roles
      $role_model = new Role($this->app);
      foreach($data->role as $r) {
        $role = $role_model->fetchRole(['role' => $r]);
        if($role) {
          $role_ids[] = $role->id;
        }
      }
    }

    // save in database
    $insert = $this->model->user->newUser($data->username, $data->email, null, $role_ids, $this->loggedInUser->id);
    if(!$insert) {
      $this->app->flash->set('Server error while creating user', 'error', '/users');
      return;
    }

    // generate token
    $token = Text::randomString('alnum', 48, true);

    // save request
    $forgot_model = new Forgot($this->app);
    $forgot_model->newForgot($insert->id, $token, $this->loggedInUser->id);

    // TODO send email with token

    // delete resource session post data
    $this->deleteResourceSessionPostData();

    // redirect to created user
    $this->app->flash->set("User '{$data->username}' created successfully. A link to set their password has been sent to their email", 'success', '/users/'.$insert->id.'/view');
    return;
  }

  public function edit() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if user has permission to the route
    if(!$this->rbac->hasPermission('Can edit users')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get user id param
    $user_id = $this->app->route->values->user_id ?? null;
    if(empty($user_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch user
    $user = $this->model->user->fetchUser(['id' => $user_id]);
    if(empty($user)) {
      return $this->app->response->redirect('404');
    }

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', "Edit User '{$user->username}'");

    // set page title
    $this->view->setVar('pagetitle', ['Edit User '.$user->username, $this->basePageTitle]);

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'users','label'=>'Users','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'users/'.$user->id.'/view','label'=>$user->username,'type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'Edit']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // form
    $form = $this->getResourceForm('edit', 'users/'.$user->id.'/edit', $this->model->user->form, $user);
    $this->view->setVar('form', $form);

    // return form view
    return $this->view->page('resource.edit');
  }

  public function processEdit() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if user has permission to the route
    if(!$this->rbac->hasPermission('Can edit users')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get user id param
    $user_id = $this->app->route->values->user_id ?? null;
    if(empty($user_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch user
    $user = $this->model->user->fetchUser(['id' => $user_id]);
    if(empty($user)) {
      return $this->app->response->redirect('404');
    }

    // registered usernames and emails
    $registered = $this->model->user->getRegisteredUsernamesAndEmails();
    $flipped = [
      'usernames' => array_flip($registered['usernames']),
      'emails' => array_flip($registered['emails']),
    ];
    unset($flipped['usernames'][$user->username]);
    unset($flipped['emails'][$user->email]);
    $registered = [
      'usernames' => array_flip($flipped['usernames']),
      'emails' => array_flip($flipped['emails']),
    ];

    $this->model->user->form['edit']['username']['validate']['rules']['notin']['values'] = $registered['usernames'];
    $this->model->user->form['edit']['email']['validate']['rules']['notin']['values'] = $registered['emails'];

    // get and validate post data
    $validate = $this->validateResourcePostData($this->model->user->form, 'edit');
    if(!$validate->success) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Validation failed. Errors are {".json_encode($validate->errors)."}"]);
      $this->app->flash->set($validate->errors, 'error', '/users/'.$user->id.'/edit');
      return;
    }

    // data
    $data = (object) $validate->data;

    // delete session post data and cancel edition
    if($data->submit == 'cancel' || ($user->username == $data->username && $user->email == $data->email)) {
      $this->deleteResourceSessionPostData();
      return $this->app->response->redirect("/users/{$user->id}/view");
    }

    // update
    $this->model->user->updateUser(
      ['username' => $data->username, 'email' => $data->email],
      ['id' => $user->id]
    );

    // delete resource session post data
    $this->deleteResourceSessionPostData();

    // redirect to updated user
    $this->app->flash->set("User '{$user->username}' updated successfully", 'success', '/users/'.$user->id.'/view');
    return;
  }

  public function delete() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if user has permission to the route
    if(!$this->rbac->hasPermission('Can delete users')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get user id param
    $user_id = $this->app->route->values->user_id ?? null;
    if(empty($user_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch user
    $user = $this->model->user->fetchUser(['id' => $user_id]);
    if(empty($user)) {
      return $this->app->response->redirect('404');
    }

    // check if user is closed
    if($user->status == 'Closed') {
      return $this->app->response->redirect('404');
    }

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', "Delete User '{$user->username}'");

    // set page title
    $this->view->setVar('pagetitle', ['Delete User '.$user->username, $this->basePageTitle]);

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'users','label'=>'Users','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'users/'.$user->id.'/view','label'=>$user->username,'type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'Delete']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // form
    $form = $this->getDeleteForm($this->model->user->form, 'users/'.$user->id.'/delete');
    $this->view->setVar('form', $form);
    $this->view->setVar('resource_type', $this->resource_type);

    // return form view
    $this->view->setVar('resource', $user);
    $this->view->setVar('columns', $this->model->user->listing);
    return $this->view->page('resource.delete');
  }

  public function processDelete() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if user has permission to the route
    if(!$this->rbac->hasPermission('Can delete users')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get user id param
    $user_id = $this->app->route->values->user_id ?? null;
    if(empty($user_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch user
    $user = $this->model->user->fetchUser(['id' => $user_id]);
    if(empty($user)) {
      return $this->app->response->redirect('404');
    }

    // check if user is closed
    if($user->status == 'Closed') {
      return $this->app->response->redirect('404');
    }

    // get post data
    $post = $this->app->request->getPostParams();
    $post = (object) $this->expectedOnly(['submit'], $post);

    // cancel form
    if(isset($post->submit) && $post->submit != 'delete') {
      $this->app->response->redirect("/users/{$user->id}/view");
      return $this->app->response;
    }

    // delete
    $delete_user = $this->model->user->deleteUser(['id' => $user->id],['limit' => 1]);
    if($delete_user === false) {
      $this->app->flash->set("Server error while deleting user '{$user->username}'", 'error', "/users/{$user->id}/view");
      return;
    }

    // delete user roles and permissions
    $this->model->user->deleteUserRoles($user->id);

    // redirect to deleted user
    $this->app->flash->set("User '{$user->username}' deleted successfully", 'success', '/users');
    return;
  }

  public function enable() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if user has permission to the route
    if(!$this->rbac->hasPermission('Can enable users')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get user id param
    $user_id = $this->app->route->values->user_id ?? null;
    if(empty($user_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch user
    $user = $this->model->user->fetchUser(['id' => $user_id]);
    if(empty($user)) {
      return $this->app->response->redirect('404');
    }

    // check if user is closed
    if($user->status == 'closed') {
      return $this->app->response->redirect('404');
    }

    // check if user is already enabled
    if($user->status == 'Active' || $user->status == 'Created') {
      $this->app->flash->set("User '{$user->username}' is already enabled", 'error', '/users/'.$user->id.'/view');
    }

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', "Enable User '{$user->username}'");

    // set page title
    $this->view->setVar('pagetitle', ['Enable User '.$user->username, $this->basePageTitle]);

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'users','label'=>'Users','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'users/'.$user->id.'/view','label'=>$user->username,'type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'Enable']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // form
    $form = $this->getEnableForm($this->model->user->form, 'users/'.$user->id.'/enable');
    $this->view->setVar('form', $form);
    $this->view->setVar('resource_type', $this->resource_type);

    // return form view
    $this->view->setVar('resource', $user);
    $this->view->setVar('columns', $this->model->user->listing);
    return $this->view->page('resource.enable');
  }

  public function processEnable() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if user has permission to the route
    if(!$this->rbac->hasPermission('Can enable users')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get user id param
    $user_id = $this->app->route->values->user_id ?? null;
    if(empty($user_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch user
    $user = $this->model->user->fetchUser(['id' => $user_id]);
    if(empty($user)) {
      return $this->app->response->redirect('404');
    }

    // check if user is closed
    if($user->status == 'closed') {
      return $this->app->response->redirect('404');
    }

    // check if user is already enabled
    if($user->status == 'Active' || $user->status == 'Created') {
      $this->app->flash->set("User '{$user->username}' is already enabled", 'error', '/users/'.$user->id.'/view');
    }

    // get post data
    $post = $this->app->request->getPostParams();
    $post = (object) $this->expectedOnly(['submit'], $post);

    // cancel form
    if(isset($post->submit) && $post->submit != 'enable') {
      $this->app->response->redirect("/users/{$user->id}/view");
      return $this->app->response;
    }

    // process enable
    $enable = $this->model->user->enableUser(['id' => $user->id]);
    if($enable === false) {
      $this->app->flash->set("Server error while activating user '{$user->username}'", 'error', "/users/{$user->id}/view");
      return;
    }

    // redirect to user
    $this->app->flash->set("User '{$user->username}' enabled successfully", 'success', "/users/{$user->id}/view");
    return;
  }

  public function disable() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if user has permission to the route
    if(!$this->rbac->hasPermission('Can disable users')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get user id param
    $user_id = $this->app->route->values->user_id ?? null;
    if(empty($user_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch user
    $user = $this->model->user->fetchUser(['id' => $user_id]);
    if(empty($user)) {
      return $this->app->response->redirect('404');
    }

    // check if user is closed
    if($user->status == 'closed') {
      return $this->app->response->redirect('404');
    }

    // check if user is already enabled
    if($user->status == 'Inactive') {
      $this->app->flash->set("User '{$user->username}' is already disabled", 'error', '/users/'.$user->id.'/view');
    }

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', "Disable User '{$user->username}'");

    // set page title
    $this->view->setVar('pagetitle', ['Disable User '.$user->username, $this->basePageTitle]);

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'users','label'=>'Users','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'users/'.$user->id.'/view','label'=>$user->username,'type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'Disable']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // form
    $form = $this->getDisableForm($this->model->user->form, 'users/'.$user->id.'/disable');
    $this->view->setVar('form', $form);
    $this->view->setVar('resource_type', $this->resource_type);

    // return form view
    $this->view->setVar('resource', $user);
    $this->view->setVar('columns', $this->model->user->listing);
    return $this->view->page('resource.disable');
  }

  public function processDisable() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if user has permission to the route
    if(!$this->rbac->hasPermission('Can disable users')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get user id param
    $user_id = $this->app->route->values->user_id ?? null;
    if(empty($user_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch user
    $user = $this->model->user->fetchUser(['id' => $user_id]);
    if(empty($user)) {
      return $this->app->response->redirect('404');
    }

    // check if user is closed
    if($user->status == 'closed') {
      return $this->app->response->redirect('404');
    }

    // check if user is already enabled
    if($user->status == 'Inactive') {
      $this->app->flash->set("User '{$user->username}' is already disabled", 'error', '/users/'.$user->id.'/view');
    }

    // get post data
    $post = $this->app->request->getPostParams();
    $post = (object) $this->expectedOnly(['submit'], $post);

    // cancel form
    if(isset($post->submit) && $post->submit != 'disable') {
      $this->app->response->redirect("/users/{$user->id}/view");
      return $this->app->response;
    }

    // disable
    $disable = $this->model->user->disableUser(['id' => $user->id]);
    if($disable === false) {
      $this->app->flash->set("Server error while deactivating user '{$user->username}'", 'error', "/users/{$user->id}/view");
      return;
    }

    // redirect to user
    $this->app->flash->set("User '{$user->username}' disabled successfully", 'success', "/users/{$user->id}/view");
    return;
  }

  public function close() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if user has permission to the route
    if(!$this->rbac->hasPermission('Can close users')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get user id param
    $user_id = $this->app->route->values->user_id ?? null;
    if(empty($user_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch user
    $user = $this->model->user->fetchUser(['id' => $user_id]);
    if(empty($user)) {
      return $this->app->response->redirect('404');
    }

    // check if user is already closed
    if($user->status == 'Closed') {
      $this->app->flash->set("User '{$user->username}' is already closed", 'error', '/users/'.$user->id.'/view');
    }

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', "Permanently Close User '{$user->username}'");

    // set page title
    $this->view->setVar('pagetitle', ['Permanently Close User '.$user->username, $this->basePageTitle]);

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'users','label'=>'Users','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'users/'.$user->id.'/view','label'=>$user->username,'type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'Close']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // form
    $form = $this->getCloseForm($this->model->user->form, 'users/'.$user->id.'/close');
    $this->view->setVar('form', $form);
    $this->view->setVar('resource_type', $this->resource_type);

    // return form view
    $this->view->setVar('resource', $user);
    $this->view->setVar('columns', $this->model->user->listing);
    return $this->view->page('resource.close');
  }

  public function processClose() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if user has permission to the route
    if(!$this->rbac->hasPermission('Can close users')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get user id param
    $user_id = $this->app->route->values->user_id ?? null;
    if(empty($user_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch user
    $user = $this->model->user->fetchUser(['id' => $user_id]);
    if(empty($user)) {
      return $this->app->response->redirect('404');
    }

    // check if user is already closed
    if($user->status == 'Closed') {
      $this->app->flash->set("User '{$user->username}' is already closed", 'error', '/users/'.$user->id.'/view');
    }

    // get post data
    $post = $this->app->request->getPostParams();
    $post = (object) $this->expectedOnly(['submit'], $post);

    // cancel form
    if(isset($post->submit) && $post->submit != 'close') {
      $this->app->response->redirect("/users/{$user->id}/view");
      return $this->app->response;
    }

    // close
    $close = $this->model->user->closeUser(['id' => $user->id]);
    if($close === false) {
      $this->app->flash->set("Server error while closing user '{$user->username}'", 'error', "/users/{$user->id}/view");
      return;
    }

    // delete user roles and permissions
    $this->model->user->deleteUserRoles($user->id);

    // redirect to user
    $this->app->flash->set("User '{$user->username}' permanently closed", 'success', "/users/{$user->id}/view");
    return;
  }

  public function roles() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if user has permission to the route
    if(!$this->rbac->hasPermission(['Can manage user roles'])) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get user id param
    $user_id = $this->app->route->values->user_id ?? null;
    if(empty($user_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch user
    $user = $this->model->user->fetchUser(['id' => $user_id]);
    if(empty($user)) {
      return $this->app->response->redirect('404');
    }

    // check if user is closed
    if($user->status == 'Closed') {
      return $this->app->response->redirect('404');
    }

    // fetch user roles
    $this->model->role = new Role($this->app);
    $user_roles = $this->model->role->loadUserRoles($user->id);
    $user->roles = [];
    foreach($user_roles as $rp) {
      $user->roles[] = $rp->id;
    }
    $this->view->setVar('user', $user);

    // fetch all roles
    $roles = $this->model->role->fetchRoles([], ['perPage' => 'all']);
    $formatted_roles = [];
    foreach($roles->rows as $r) {
      $formatted_roles[$r->id] = $r;
    }
    $this->view->setVar('roles', json_decode(json_encode($formatted_roles)));

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', "Manage Roles for User '{$user->username}'");

    // set page title
    $this->view->setVar('pagetitle', ['Manage Roles for User '.$user->username, $this->basePageTitle]);

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'users','label'=>'Users','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'users/'.$user->id.'/view','label'=>$user->username,'type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'Manage Roles']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // view
    return $this->view->page('users.roles');
  }

  public function processRoles() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if user has permission to the route
    if(!$this->rbac->hasPermission(['Can manage user roles'])) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get user id param
    $user_id = $this->app->route->values->user_id ?? null;
    if(empty($user_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch user
    $user = $this->model->user->fetchUser(['id' => $user_id]);
    if(empty($user)) {
      return $this->app->response->redirect('404');
    }

    // check if user is closed
    if($user->status == 'Closed') {
      return $this->app->response->redirect('404');
    }

    // fetch user roles
    $this->model->role = new Role($this->app);
    $roles = $this->model->role->loadUserRoles($user->id);
    $role_ids = [];
    foreach($roles as $r) {
      $role_ids[] = $r->id;
    }

    // post data
    $post = $this->app->request->getPostParams();
    $post = (object) $this->expectedOnly(['role'], $post);

    // save roles
    $this->model->role = new Role($this->app);
    $this->model->role->saveRoles($user->id, $this->loggedInUser->id, $post->role, array_diff($post->role, $role_ids));

    // redirect to user
    $this->app->flash->set("Roles for User '{$user->username}' updated successfully", 'success', '/users/'.$user->id.'/roles');
    return;
  }

  private function buildUserModelForm() {

    // build user resource form - role
    $role_model = new Role($this->app);
    $roles = $role_model->getRolesAsOptions();
    $this->model->user->form['resource']['role']['options'] = $roles->options;
    $this->model->user->form['resource']['role']['validate']['rules']['in']['values'] = $roles->values;
  }

}
