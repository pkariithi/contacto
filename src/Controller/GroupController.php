<?php

namespace SMVC\Controller;

use SMVC\Core\Controller;
use SMVC\Model\Group;
use SMVC\Model\Messaging;
use SMVC\Model\Contact;
use SMVC\Model\Upload;

use SMVC\Helpers\Pdf;
use SMVC\Helpers\Pager;
use SMVC\Helpers\Validate;

class GroupController extends Controller {

  public function __construct($app) {
    parent::__construct($app);

    // set partial to use as main
    $this->view->setPartial('main');

    // set base page title
    $this->basePageTitle = 'Contact Groups';

    // active menu
    $this->view->setVar('active', 'groups');

    // group model
    $this->model->group = new Group($app);
    $this->view->setVar('module', $this->model->group->module);

    // resource name
    $this->resource_name = 'groups';
    $this->resource_type = 'group';
    $this->view->setVar('resource_name', $this->resource_name);
  }

  public function listing() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if group has permission to the route
    if(!$this->rbac->hasPermission('Can view groups')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // load options
    $options = $this->loadOptions($this->model->group->listing);

    // reset
    if(isset($options['submit']) && $options['submit'] == 'reset') {
      $this->app->response->redirect('/groups');
    }

    // fetch groups
    $groups = $this->model->group->fetchGroups([], $options);

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', 'Manage Contact Groups');

    // filter form
    $filter_form = $this->getResourceFilterform('groups', $this->model->group->listing, $options);
    $this->view->setVar('filter_form', $filter_form);

    // links
    $links = $this->getLinksMarkup($this->model->group->links, $groups->rows);
    foreach($links as $name => $markup) {
      if(!empty($markup)) {
        $this->view->setVar($name, $markup);
      }
    }

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'Groups']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // pager
    $pager = Pager::paginate(
      $groups->meta->count,
      $groups->meta->page,
      $groups->meta->pages,
      'groups/',
      $this->app->request->getGetParamsAsUri()
    );
    $this->view->setVar('pager', $pager);

    // set page title
    $this->view->setVar('pagetitle', [$this->basePageTitle]);

    // return form view
    $this->view->setVar('records', $groups->rows);
    $this->view->setVar('columns', $this->model->group->listing);
    return $this->view->page('resource.listing');
  }

  public function view() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if group has permission to the route
    if(!$this->rbac->hasPermission('Can view groups')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get group id param
    $group_id = $this->app->route->values->group_id ?? null;
    if(empty($group_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch group
    $group = $this->model->group->fetchGroup(['id' => $group_id]);
    if(!isset($group->id)) {
      return $this->app->response->redirect('404');
    }

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', $group->name);

    // links
    $links = $this->getLinksMarkup($this->model->group->links, [$group]);
    foreach($links as $name => $markup) {
      if(!empty($markup)) {
        $this->view->setVar($name, $markup);
      }
    }

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'groups','label'=>'Groups','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>$group->name]);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // set page title
    $this->view->setVar('pagetitle', [$group->name, $this->basePageTitle]);

    // return detail view
    $this->view->setVar('resource', $group);
    $this->view->setVar('columns', !empty($this->model->group->detail) ? $this->model->group->detail : $this->model->group->listing);
    return $this->view->page('resource.detail');
  }

  public function new() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if group has permission to the route
    if(!$this->rbac->hasPermission('Can add groups')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', 'New Group');

    // set page title
    $this->view->setVar('pagetitle', ['New Group', $this->basePageTitle]);

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'groups','label'=>'Groups','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'New Group']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // form
    $form = $this->getResourceForm('new','groups/new', $this->model->group->form);
    $this->view->setVar('form', $form);

    // return form view
    return $this->view->page('resource.new');
  }

  public function processNew() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if group has permission to the route
    if(!$this->rbac->hasPermission('Can add groups')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // registered group names
    $registered = $this->model->group->getRegisteredGroupnames();
    $this->model->group->form['resource']['name']['validate']['rules']['notin']['values'] = $registered;

    // get and validate post data
    $validate = $this->validateResourcePostData($this->model->group->form);
    if(!$validate->success) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Validation failed. Errors are {".json_encode($validate->errors)."}"]);
      $this->app->flash->set($validate->errors, 'error', '/groups/new');
      return;
    }

    // data
    $data = (object) $validate->data;

    // delete session post data and cancel edition
    if($data->submit != 'new') {
      $this->deleteResourceSessionPostData();
      return $this->app->response->redirect("/groups");
    }

    // save in database
    $insert = $this->model->group->newGroup($data->name, $data->comments, $this->loggedInUser->id);
    if(!$insert) {
      $this->app->flash->set('Server error while creating group', 'error', '/groups');
      return;
    }

    // delete resource session post data
    $this->deleteResourceSessionPostData();

    // redirect to created group
    $this->app->flash->set("Group '{$data->name}' created successfully", 'success', '/groups/'.$insert.'/view');
    return;
  }

  public function upload() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if contact has permission to the route
    if(!$this->rbac->hasPermission('Can bulk upload contacts')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get group id param
    $group_id = $this->app->route->values->group_id ?? null;
    if(empty($group_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch group
    $group = $this->model->group->fetchGroup(['id' => $group_id]);
    if(empty($group)) {
      return $this->app->response->redirect('404');
    }

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', "Upload Bulk Contacts to Group '{$group->name}'");

    // set page title
    $this->view->setVar('pagetitle', ["Upload Bulk Contacts to Group '{$group->name}'", $this->basePageTitle]);

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'groups','label'=>'Groups','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'groups/'.$group->id.'/view','label'=>$group->name,'type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'Upload Bulk Contacts']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // sample bulk files
    $this->view->setSampleBulkFiles();

    // form
    $form = $this->getUploadForm("groups/{$group->id}/upload");
    $this->view->setVar('form', $form);

    // return form view
    return $this->view->page('resource.upload');
  }

  public function processUpload() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if contact has permission to the route
    if(!$this->rbac->hasPermission('Can bulk upload contacts')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get group id param
    $group_id = $this->app->route->values->group_id ?? null;
    if(empty($group_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch group
    $group = $this->model->group->fetchGroup(['id' => $group_id]);
    if(empty($group)) {
      return $this->app->response->redirect('404');
    }

    // get post
    $expected = ['comments','submit'];
    $post = $this->app->request->getPostParams();
    $post = $this->expectedOnly($expected, $post);

    // delete session post data and cancel edition
    if($post['submit'] != 'upload') {
      $this->deleteResourceSessionPostData();
      return $this->app->response->redirect("/groups/{$group->id}/view");
    }

    // validate uploaded file
    $file = $this->verifyAndUploadBulkFile();

    //comments
    $comments = $post['comments'] ?? null;

    // save file details to database, before processing file
    $this->model->upload = new Upload($this->app);
    $upload_id = $this->model->upload->newUpload($file, $comments, $group->id, $this->loggedInUser->id);

    // read file and process contacts
    $contacts = $this->readBulkFile($file['new_file_name'], $file['extension']);

    // TODO we assume all contacts are valid

    // save contacts in database
    $this->model->contact = new Contact($this->app);
    $inserted = $this->model->contact->bulkInsert($contacts, $upload_id, $this->loggedInUser->id);

    if($inserted) {

      // process the contact group mapping
      $inserted_contacts = $this->model->contact->fetchContacts(
        ['bulk_upload_id' => $upload_id], ['perPage' => 'all']
      );
      $inserted_contact_ids = [];
      foreach($inserted_contacts->rows as $ic) {
        $inserted_contact_ids[] = $ic->id;
      }
      $this->model->group->saveGroupContacts($group->id, $this->loggedInUser->id, [], $inserted_contact_ids);

      // delete resource session post data
      $this->deleteResourceSessionPostData();

      // redirect to groups
      $this->app->flash->set("Bulk contacts created successfully", 'success', "/groups/{$group->id}/contacts");
      return;
    } else {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Bulk insertion failed"]);
      $this->app->flash->set(['A server error occured while processing the bulk insertion','Kindly try again later'], 'error', "/groups/{$group->id}/view");
      return;
    }
  }

  public function export() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if group has permission to the route
    if(!$this->rbac->hasPermission(['Can export groups'])) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // fetch groups
    $groups = $this->model->group->fetchGroups([], ["perPage" => "all"]);

    // export
    $pdf = new Pdf();
    return $pdf->exportListing(
      'Contact Groups',
      $this->model->group->listing,
      $groups->rows
    );
  }

  public function edit() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if group has permission to the route
    if(!$this->rbac->hasPermission('Can edit groups')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get group id param
    $group_id = $this->app->route->values->group_id ?? null;
    if(empty($group_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch group
    $group = $this->model->group->fetchGroup(['id' => $group_id]);
    if(empty($group)) {
      return $this->app->response->redirect('404');
    }

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', "Edit Group '{$group->name}'");

    // set page title
    $this->view->setVar('pagetitle', ['Edit Group '.$group->name, $this->basePageTitle]);

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'groups','label'=>'Groups','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'groups/'.$group->id.'/view','label'=>$group->name,'type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'Edit']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // form
    $form = $this->getResourceForm('edit', 'groups/'.$group->id.'/edit', $this->model->group->form, $group);
    $this->view->setVar('form', $form);

    // return form view
    return $this->view->page('resource.edit');
  }

  public function processEdit() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if group has permission to the route
    if(!$this->rbac->hasPermission('Can edit groups')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get group id param
    $group_id = $this->app->route->values->group_id ?? null;
    if(empty($group_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch group
    $group = $this->model->group->fetchGroup(['id' => $group_id]);
    if(empty($group)) {
      return $this->app->response->redirect('404');
    }

    // registered names
    $registered = $this->model->group->getRegisteredGroupnames();
    $flipped = array_flip($registered);
    unset($flipped[$group->name]);
    $registered = array_flip($flipped);

    $this->model->group->form['edit']['name']['validate']['rules']['notin']['values'] = $registered;

    // get and validate post data
    $validate = $this->validateResourcePostData($this->model->group->form, 'edit');
    if(!$validate->success) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Validation failed. Errors are {".json_encode($validate->errors)."}"]);
      $this->app->flash->set($validate->errors, 'error', '/groups/'.$group->id.'/edit');
      return;
    }

    // data
    $data = (object) $validate->data;

    // delete session post data and cancel edition
    if($data->submit == 'cancel' || $group->name == $data->name) {
      $this->deleteResourceSessionPostData();
      return $this->app->response->redirect("/groups/{$group->id}/view");
    }

    // update
    $this->model->group->updateGroup(
      ['name' => $data->name, 'comments' => $data->comments],
      ['id' => $group->id]
    );

    // delete resource session post data
    $this->deleteResourceSessionPostData();

    // redirect to updated group
    $this->app->flash->set("Group '{$group->name}' updated successfully", 'success', '/groups/'.$group->id.'/view');
    return;
  }

  public function delete() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if group has permission to the route
    if(!$this->rbac->hasPermission('Can delete groups')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get group id param
    $group_id = $this->app->route->values->group_id ?? null;
    if(empty($group_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch group
    $group = $this->model->group->fetchGroup(['id' => $group_id]);
    if(empty($group)) {
      return $this->app->response->redirect('404');
    }

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', "Delete Group '{$group->name}'");

    // set page title
    $this->view->setVar('pagetitle', ['Delete Group '.$group->name, $this->basePageTitle]);

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'groups','label'=>'Groups','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'groups/'.$group->id.'/view','label'=>$group->name,'type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'Delete']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // form
    $form = $this->getDeleteForm($this->model->group->form, 'groups/'.$group->id.'/delete');
    $this->view->setVar('form', $form);
    $this->view->setVar('resource_type', $this->resource_type);

    // return form view
    $this->view->setVar('resource', $group);
    $this->view->setVar('columns', $this->model->group->listing);
    return $this->view->page('resource.delete');
  }

  public function processDelete() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if group has permission to the route
    if(!$this->rbac->hasPermission('Can delete groups')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get group id param
    $group_id = $this->app->route->values->group_id ?? null;
    if(empty($group_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch group
    $group = $this->model->group->fetchGroup(['id' => $group_id]);
    if(empty($group)) {
      return $this->app->response->redirect('404');
    }

    // get post data
    $post = $this->app->request->getPostParams();
    $post = (object) $this->expectedOnly(['submit'], $post);

    // cancel form
    if(isset($post->submit) && $post->submit != 'delete') {
      $this->app->response->redirect("/groups/{$group->id}/view");
      return $this->app->response;
    }

    // delete
    $delete_group = $this->model->group->deleteGroup(['id' => $group->id],['limit' => 1]);
    if($delete_group === false) {
      $this->app->flash->set("Server error while deleting group '{$group->name}'", 'error', "/groups/{$group->id}/view");
      return;
    }

    // delete group contact mapping
    $this->model->group->deleteGroupContacts($group->id);

    // redirect to deleted group
    $this->app->flash->set("Group '{$group->name}' deleted successfully", 'success', '/groups');
    return;
  }

  public function enable() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if group has permission to the route
    if(!$this->rbac->hasPermission('Can enable groups')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get group id param
    $group_id = $this->app->route->values->group_id ?? null;
    if(empty($group_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch group
    $group = $this->model->group->fetchGroup(['id' => $group_id]);
    if(empty($group)) {
      return $this->app->response->redirect('404');
    }

    // check if group is already enabled
    if($group->status == 'Active') {
      $this->app->flash->set("Group '{$group->name}' is already enabled", 'error', '/groups/'.$group->id.'/view');
    }

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', "Enable Group '{$group->name}'");

    // set page title
    $this->view->setVar('pagetitle', ['Enable Group '.$group->name, $this->basePageTitle]);

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'groups','label'=>'Groups','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'groups/'.$group->id.'/view','label'=>$group->name,'type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'Enable']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // form
    $form = $this->getEnableForm($this->model->group->form, 'groups/'.$group->id.'/enable');
    $this->view->setVar('form', $form);
    $this->view->setVar('resource_type', $this->resource_type);

    // return form view
    $this->view->setVar('resource', $group);
    $this->view->setVar('columns', $this->model->group->listing);
    return $this->view->page('resource.enable');
  }

  public function processEnable() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if group has permission to the route
    if(!$this->rbac->hasPermission('Can enable groups')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get group id param
    $group_id = $this->app->route->values->group_id ?? null;
    if(empty($group_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch group
    $group = $this->model->group->fetchGroup(['id' => $group_id]);
    if(empty($group)) {
      return $this->app->response->redirect('404');
    }

    // check if group is already enabled
    if($group->status == 'Active') {
      $this->app->flash->set("Group '{$group->name}' is already enabled", 'error', '/groups/'.$group->id.'/view');
    }

    // get post data
    $post = $this->app->request->getPostParams();
    $post = (object) $this->expectedOnly(['submit'], $post);

    // cancel form
    if(isset($post->submit) && $post->submit != 'enable') {
      $this->app->response->redirect("/groups/{$group->id}/view");
      return $this->app->response;
    }

    // process enable
    $enable = $this->model->group->enableGroup(['id' => $group->id]);
    if($enable === false) {
      $this->app->flash->set("Server error while activating group '{$group->name}'", 'error', "/groups/{$group->id}/view");
      return;
    }

    // redirect to group
    $this->app->flash->set("Group '{$group->name}' enabled successfully", 'success', "/groups/{$group->id}/view");
    return;
  }

  public function disable() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if group has permission to the route
    if(!$this->rbac->hasPermission('Can disable groups')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get group id param
    $group_id = $this->app->route->values->group_id ?? null;
    if(empty($group_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch group
    $group = $this->model->group->fetchGroup(['id' => $group_id]);
    if(empty($group)) {
      return $this->app->response->redirect('404');
    }

    // check if group is already enabled
    if($group->status == 'Inactive') {
      $this->app->flash->set("Group '{$group->name}' is already disabled", 'error', '/groups/'.$group->id.'/view');
    }

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', "Disable Group '{$group->name}'");

    // set page title
    $this->view->setVar('pagetitle', ['Disable Group '.$group->name, $this->basePageTitle]);

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'groups','label'=>'Groups','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'groups/'.$group->id.'/view','label'=>$group->name,'type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'Disable']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // form
    $form = $this->getDisableForm($this->model->group->form, 'groups/'.$group->id.'/disable');
    $this->view->setVar('form', $form);
    $this->view->setVar('resource_type', $this->resource_type);

    // return form view
    $this->view->setVar('resource', $group);
    $this->view->setVar('columns', $this->model->group->listing);
    return $this->view->page('resource.disable');
  }

  public function processDisable() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if group has permission to the route
    if(!$this->rbac->hasPermission('Can disable groups')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get group id param
    $group_id = $this->app->route->values->group_id ?? null;
    if(empty($group_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch group
    $group = $this->model->group->fetchGroup(['id' => $group_id]);
    if(empty($group)) {
      return $this->app->response->redirect('404');
    }

    // check if group is already enabled
    if($group->status == 'Inactive') {
      $this->app->flash->set("Group '{$group->name}' is already disabled", 'error', '/groups/'.$group->id.'/view');
    }

    // get post data
    $post = $this->app->request->getPostParams();
    $post = (object) $this->expectedOnly(['submit'], $post);

    // cancel form
    if(isset($post->submit) && $post->submit != 'disable') {
      $this->app->response->redirect("/groups/{$group->id}/view");
      return $this->app->response;
    }

    // disable
    $disable = $this->model->group->disableGroup(['id' => $group->id]);
    if($disable === false) {
      $this->app->flash->set("Server error while deactivating group '{$group->name}'", 'error', "/groups/{$group->id}/view");
      return;
    }

    // redirect to group
    $this->app->flash->set("Group '{$group->name}' disabled successfully", 'success', "/groups/{$group->id}/view");
    return;
  }

  public function contacts() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if group has permission to the route
    if(!$this->rbac->hasPermission(['Can manage group contacts'])) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get group id param
    $group_id = $this->app->route->values->group_id ?? null;
    if(empty($group_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch group
    $group = $this->model->group->fetchGroup(['id' => $group_id]);
    if(empty($group)) {
      return $this->app->response->redirect('404');
    }

    // fetch group contacts
    $group_contacts = $this->model->group->loadGroupContacts($group->id);
    $group->contacts = [];
    foreach($group_contacts as $rp) {
      $group->contacts[] = $rp->id;
    }
    $this->view->setVar('group', $group);

    // fetch all contacts
    $this->model->contact = new Contact($this->app);
    $contacts = $this->model->contact->fetchContacts([], ['perPage' => 'all']);
    $formatted_contacts = [];
    foreach($contacts->rows as $r) {
      $formatted_contacts[$r->id] = $r;
    }
    $this->view->setVar('contacts', json_decode(json_encode($formatted_contacts)));

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', "Manage Contacts for Group '{$group->name}'");

    // set page title
    $this->view->setVar('pagetitle', ['Manage Contacts for Group '.$group->name, $this->basePageTitle]);

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'groups','label'=>'Groups','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'groups/'.$group->id.'/view','label'=>$group->name,'type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'Manage Contacts']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // view
    return $this->view->page('groups.contacts');
  }

  public function processContacts() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if group has permission to the route
    if(!$this->rbac->hasPermission(['Can manage group contacts'])) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get group id param
    $group_id = $this->app->route->values->group_id ?? null;
    if(empty($group_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch group
    $group = $this->model->group->fetchGroup(['id' => $group_id]);
    if(empty($group)) {
      return $this->app->response->redirect('404');
    }

    // fetch group contacts
    $contacts = $this->model->group->loadGroupContacts($group->id);
    $contact_ids = [];
    foreach($contacts as $r) {
      $contact_ids[] = $r->id;
    }

    // post data
    $post = $this->app->request->getPostParams();
    $post = (object) $this->expectedOnly(['contact'], $post);

    // save contacts
    $this->model->group->saveGroupContacts($group->id, $this->loggedInUser->id, $post->contact, array_diff($post->contact, $contact_ids));

    // redirect to group
    $this->app->flash->set("Contacts for Group '{$group->name}' updated successfully", 'success', '/groups/'.$group->id.'/contacts');
    return;
  }

  public function sendSms() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if contact has permission to the route
    if(!$this->rbac->hasPermission('Can message all contacts in a group')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get group id param
    $group_id = $this->app->route->values->group_id ?? null;
    if(empty($group_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch group
    $group = $this->model->group->fetchGroup(['id' => $group_id]);
    if(empty($group)) {
      return $this->app->response->redirect('404');
    }

    // check if group is active
    if($group->status == 'Inactive') {
      $this->app->flash->set("Group '{$group->name}' is inactive", 'error', '/groups/'.$group->id.'/view');
    }

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', "Send SMS to Group '{$group->name}'");

    // set page title
    $this->view->setVar('pagetitle', ['Send SMS to Group '.$group->name, $this->basePageTitle]);

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'groups','label'=>'Groups','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'groups/'.$group->id.'/view','label'=>$group->name,'type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'Send SMS']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // form
    $form = $this->getSmsForm($this->model->group->form, 'groups/'.$group->id.'/send-sms');
    $this->view->setVar('form', $form);
    $this->view->setVar('resource_type', $this->resource_type);

    // return form view
    return $this->view->page('messaging.sms');
  }

  public function processSendSms() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if group has permission to the route
    if(!$this->rbac->hasPermission('Can message all contacts in a group')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get group id param
    $group_id = $this->app->route->values->group_id ?? null;
    if(empty($group_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch group
    $group = $this->model->group->fetchGroup(['id' => $group_id]);
    if(empty($group)) {
      return $this->app->response->redirect('404');
    }

    // check if group is active
    if($group->status == 'Inactive') {
      $this->app->flash->set("Group '{$group->name}' is inactive", 'error', '/groups/'.$group->id.'/view');
    }

    // get post data
    $expected = ['message','submit'];
    $post = $this->app->request->getPostParams();
    $post = $this->expectedOnly($expected, $post);

    // delete session post data and cancel edition
    if($post['submit'] != 'sms') {
      $this->deleteResourceSessionPostData();
      return $this->app->response->redirect('groups/'.$group->id.'/view');
    }

    // save to session
    $this->app->session->set('post', [
      $this->resource_type => (object) ['message' => $post['message']]
    ]);

    // validate
    $validation_rules = [
      'message' => [
        'value' => trim($post['message']),
        'rules' => [
          'required' => [
            'msg' => 'The message is required'
          ],
          'maxLen' => [
            'length' => 160,
            'msg' => 'The length of the message should not exceed 160 characters'
          ],
        ]
      ]
    ];
    $validate = Validate::run($validation_rules);

    // if valid data
    if($validate->success) {

      // data
      $data = (object) $validate->data;

      // load contact ids from group
      $contacts = $this->model->group->loadGroupContacts($group->id);
      $contact_ids = [];
      foreach($contacts as $c) {
        $contact_ids[] = $c->id;
      }

      // save in database
      $this->model->messaging = new Messaging($this->app);
      $insert = $this->model->messaging->newMessaging(null, $data->message, $contact_ids, $group->id, $this->loggedInUser->id);
      if(!$insert) {
        $this->app->flash->set('Server error while sending SMS', 'error', '/groups');
        return;
      }

      // TODO send actual SMS

      // delete resource session post data
      $this->deleteResourceSessionPostData();

      // redirect to created group
      $this->app->flash->set("SMS to '{$group->name}' sent successfully", 'success', '/groups/'.$group->id.'/view');
      return;

    } else {
      // redirect back with validation errors
      $this->app->log->error(['ResponseCode'=>400,'TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"validation failed: ".json_encode($validate->errors)]);
      $this->app->flash->set($validate->errors, 'error', '/groups/'.$group->id.'/send-sms');
    }
  }

  public function sendEmail() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if group has permission to the route
    if(!$this->rbac->hasPermission('Can message all contacts in a group')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get group id param
    $group_id = $this->app->route->values->group_id ?? null;
    if(empty($group_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch group
    $group = $this->model->group->fetchGroup(['id' => $group_id]);
    if(empty($group)) {
      return $this->app->response->redirect('404');
    }

    // check if group is active
    if($group->status == 'Inactive') {
      $this->app->flash->set("Group '{$group->name}' is inactive", 'error', '/groups/'.$group->id.'/view');
    }

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', "Send Email to Group '{$group->name}'");

    // set page title
    $this->view->setVar('pagetitle', ['Send Email to Group '.$group->name, $this->basePageTitle]);

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'groups','label'=>'Groups','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'groups/'.$group->id.'/view','label'=>$group->name,'type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'Send Email']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // form
    $form = $this->getEmailForm($this->model->group->form, 'groups/'.$group->id.'/send-email');
    $this->view->setVar('form', $form);
    $this->view->setVar('resource_type', $this->resource_type);

    // return form view
    $this->view->setVar('resource', $group);
    $this->view->setVar('columns', $this->model->group->listing);
    return $this->view->page('messaging.email');
  }

  public function processSendEmail() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if group has permission to the route
    if(!$this->rbac->hasPermission('Can message all contacts in a group')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get group id param
    $group_id = $this->app->route->values->group_id ?? null;
    if(empty($group_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch group
    $group = $this->model->group->fetchGroup(['id' => $group_id]);
    if(empty($group)) {
      return $this->app->response->redirect('404');
    }

    // check if group is already enabled
    if($group->status == 'Inactive') {
      $this->app->flash->set("Group '{$group->surname}' is disabled", 'error', '/groups/'.$group->id.'/view');
    }

    // get post data
    $expected = ['subject','message','submit'];
    $post = $this->app->request->getPostParams();
    $post = $this->expectedOnly($expected, $post);

    // delete session post data and cancel edition
    if($post['submit'] != 'email') {
      $this->deleteResourceSessionPostData();
      return $this->app->response->redirect('groups/'.$group->id.'/view');
    }

    // save to session
    $this->app->session->set('post', [
      $this->resource_type => (object) ['subject' => $post['subject'], 'message' => $post['message']]
    ]);

    // validate
    $validation_rules = [
      'subject' => [
        'value' => trim($post['subject']),
        'rules' => [
          'required' => [
            'msg' => 'The subject is required'
          ],
          'maxLen' => [
            'length' => 80,
            'msg' => 'The length of the subject should not exceed 80 characters'
          ],
        ]
      ],
      'message' => [
        'value' => trim($post['message']),
        'rules' => [
          'required' => [
            'msg' => 'The message is required'
          ]
        ]
      ]
    ];
    $validate = Validate::run($validation_rules);

    // if valid data
    if($validate->success) {

      // data
      $data = (object) $validate->data;

      // load contact ids from group
      $contacts = $this->model->group->loadGroupContacts($group->id);
      $contact_ids = [];
      foreach($contacts as $c) {
        $contact_ids[] = $c->id;
      }

      // save in database
      $this->model->messaging = new Messaging($this->app);
      $insert = $this->model->messaging->newMessaging($data->subject, $data->message, $contact_ids, $group->id,$this->loggedInUser->id);
      if(!$insert) {
        $this->app->flash->set('Server error while sending Email', 'error', '/groups');
        return;
      }

      // TODO send actual Email

      // delete resource session post data
      $this->deleteResourceSessionPostData();

      // redirect to created group
      $this->app->flash->set("Email to '{$group->name}' sent successfully", 'success', '/groups/'.$group->id.'/view');
      return;

    } else {
      // redirect back with validation errors
      $this->app->log->error(['ResponseCode'=>400,'TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"validation failed: ".json_encode($validate->errors)]);
      $this->app->flash->set($validate->errors, 'error', '/groups/'.$group->id.'/send-email');
    }
  }

   public function exportContacts() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if group has permission to the route
    if(!$this->rbac->hasPermission(['Can export groups'])) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get group id param
    $group_id = $this->app->route->values->group_id ?? null;
    if(empty($group_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch group
    $group = $this->model->group->fetchGroup(['id' => $group_id]);
    if(empty($group)) {
      return $this->app->response->redirect('404');
    }

    // fetch group contact ids
    $contacts = $this->model->group->loadGroupContacts($group->id);
    $contact_ids = [];
    foreach($contacts as $r) {
      $contact_ids[] = $r->id;
    }

    // load contacts by ids
    $this->model->contact = new Contact($this->app);
    $contacts = $this->model->contact->fetchContacts(
      ["contacts.id IN (".implode(", ", $contact_ids).")"],
      ["perPage" => "all"]
    );

    // export
    $pdf = new Pdf();
    return $pdf->exportListing(
      'Group Contacts: '.$group->name,
      $this->model->contact->listing,
      $contacts->rows
    );
  }

}
