<?php

namespace SMVC\Controller;

use SMVC\Core\Controller;
use SMVC\Model\Contact;
use SMVC\Model\Messaging;
use SMVC\Model\Group;
use SMVC\Model\Upload;

use SMVC\Helpers\Pdf;
use SMVC\Helpers\Pager;
use SMVC\Helpers\Validate;

class ContactController extends Controller {

  public function __construct($app) {
    parent::__construct($app);

    // set partial to use as main
    $this->view->setPartial('main');

    // set base page title
    $this->basePageTitle = 'Contacts';

    // active menu
    $this->view->setVar('active', 'contacts');

    // contact model
    $this->model->contact = new Contact($app);
    $this->view->setVar('module', $this->model->contact->module);

    // resource name
    $this->resource_name = 'contacts';
    $this->resource_type = 'contact';
    $this->view->setVar('resource_name', $this->resource_name);
  }

  public function listing() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if contact has permission to the route
    if(!$this->rbac->hasPermission('Can view contacts')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // load options
    $options = $this->loadOptions($this->model->contact->listing);

    // reset
    if(isset($options['submit']) && $options['submit'] == 'reset') {
      $this->app->response->redirect('/contacts');
    }

    // fetch contacts
    $contacts = $this->model->contact->fetchContacts([], $options);

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', 'Manage Contacts');

    // filter form
    $filter_form = $this->getResourceFilterform('contacts', $this->model->contact->listing, $options);
    $this->view->setVar('filter_form', $filter_form);

    // links
    $links = $this->getLinksMarkup($this->model->contact->links, $contacts->rows);
    foreach($links as $name => $markup) {
      if(!empty($markup)) {
        $this->view->setVar($name, $markup);
      }
    }

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'Contacts']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // pager
    $pager = Pager::paginate(
      $contacts->meta->count,
      $contacts->meta->page,
      $contacts->meta->pages,
      'contacts/',
      $this->app->request->getGetParamsAsUri()
    );
    $this->view->setVar('pager', $pager);

    // set page title
    $this->view->setVar('pagetitle', [$this->basePageTitle]);

    // return form view
    $this->view->setVar('records', $contacts->rows);
    $this->view->setVar('columns', $this->model->contact->listing);
    return $this->view->page('resource.listing');
  }

  public function view() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if contact has permission to the route
    if(!$this->rbac->hasPermission('Can view contacts')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get contact id param
    $contact_id = $this->app->route->values->contact_id ?? null;
    if(empty($contact_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch contact
    $contact = $this->model->contact->fetchContact(['id' => $contact_id]);
    if(!isset($contact->id)) {
      return $this->app->response->redirect('404');
    }

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', $contact->surname);

    // links
    $links = $this->getLinksMarkup($this->model->contact->links, [$contact]);
    foreach($links as $name => $markup) {
      if(!empty($markup)) {
        $this->view->setVar($name, $markup);
      }
    }

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'contacts','label'=>'Contacts','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>$contact->surname]);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // set page title
    $this->view->setVar('pagetitle', [$contact->surname, $this->basePageTitle]);

    // return detail view
    $this->view->setVar('resource', $contact);
    $this->view->setVar('columns', !empty($this->model->contact->detail) ? $this->model->contact->detail : $this->model->contact->listing);
    return $this->view->page('resource.detail');
  }

  public function new() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if contact has permission to the route
    if(!$this->rbac->hasPermission('Can add contacts')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', 'New Contact');

    // set page title
    $this->view->setVar('pagetitle', ['New Contact', $this->basePageTitle]);

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'contacts','label'=>'Contacts','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'New Contact']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // form
    $form = $this->getResourceForm('new','contacts/new', $this->model->contact->form);
    $this->view->setVar('form', $form);

    // return form view
    return $this->view->page('resource.new');
  }

  public function processNew() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if contact has permission to the route
    if(!$this->rbac->hasPermission('Can add contacts')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get and validate post data
    $validate = $this->validateResourcePostData($this->model->contact->form);
    if(!$validate->success) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Validation failed. Errors are {".json_encode($validate->errors)."}"]);
      $this->app->flash->set($validate->errors, 'error', '/contacts/new');
      return;
    }

    // data
    $data = (object) $validate->data;

    // delete session post data and cancel edition
    if($data->submit != 'new') {
      $this->deleteResourceSessionPostData();
      return $this->app->response->redirect("/contacts");
    }

    // save in database
    $insert = $this->model->contact->newContact($data->surname, $data->other_names, $data->email, $data->phone, $data->address, $data->comments, $this->loggedInUser->id);
    if(!$insert) {
      $this->app->flash->set('Server error while creating contact', 'error', '/contacts');
      return;
    }

    // delete resource session post data
    $this->deleteResourceSessionPostData();

    // redirect to created contact
    $this->app->flash->set("Contact '{$data->surname}' created successfully", 'success', '/contacts/'.$insert.'/view');
    return;
  }

  public function upload() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if contact has permission to the route
    if(!$this->rbac->hasPermission('Can bulk upload contacts')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', 'Upload Contacts');

    // set page title
    $this->view->setVar('pagetitle', ['Upload Contacts', $this->basePageTitle]);

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'contacts','label'=>'Contacts','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'Upload Contacts']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // sample bulk files
    $this->view->setSampleBulkFiles();

    // form
    $form = $this->getUploadForm('contacts/upload');
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

    // get post
    $expected = ['comments','submit'];
    $post = $this->app->request->getPostParams();
    $post = $this->expectedOnly($expected, $post);

    // delete session post data and cancel edition
    if($post['submit'] != 'upload') {
      $this->deleteResourceSessionPostData();
      return $this->app->response->redirect("/contacts");
    }

    // validate uploaded file
    $file = $this->verifyAndUploadBulkFile();

    //comments
    $comments = $post['comments'] ?? null;

    // save file details to database, before processing file
    $this->model->upload = new Upload($this->app);
    $upload_id = $this->model->upload->newUpload($file, $comments, null, $this->loggedInUser->id);

    // read file and process contacts
    $contacts = $this->readBulkFile($file['new_file_name'], $file['extension']);

    // TODO we assume all contacts are valid

    // save contacts in database
    $inserted = $this->model->contact->bulkInsert($contacts, $upload_id, $this->loggedInUser->id);

    if($inserted) {

      // delete resource session post data
      $this->deleteResourceSessionPostData();

      // redirect to contacts
      $this->app->flash->set("Bulk contacts created successfully", 'success', '/contacts');
      return;
    } else {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Bulk insertion failed"]);
      $this->app->flash->set(['A server error occured while processing the bulk insertion','Kindly try again later'], 'error', '/contacts/upload');
      return;
    }
  }

  public function export() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if contact has permission to the route
    if(!$this->rbac->hasPermission(['Can export contacts'])) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // fetch contacts
    $contacts = $this->model->contact->fetchContacts([], ["perPage" => "all"]);

    // export
    $pdf = new Pdf();
    return $pdf->exportListing(
      'Contacts',
      $this->model->contact->listing,
      $contacts->rows
    );
  }

  public function edit() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if contact has permission to the route
    if(!$this->rbac->hasPermission('Can edit contacts')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get contact id param
    $contact_id = $this->app->route->values->contact_id ?? null;
    if(empty($contact_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch contact
    $contact = $this->model->contact->fetchContact(['id' => $contact_id]);
    if(empty($contact)) {
      return $this->app->response->redirect('404');
    }

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', "Edit Contact '{$contact->surname}'");

    // set page title
    $this->view->setVar('pagetitle', ['Edit Contact '.$contact->surname, $this->basePageTitle]);

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'contacts','label'=>'Contacts','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'contacts/'.$contact->id.'/view','label'=>$contact->surname,'type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'Edit']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // form
    $form = $this->getResourceForm('edit', 'contacts/'.$contact->id.'/edit', $this->model->contact->form, $contact);
    $this->view->setVar('form', $form);

    // return form view
    return $this->view->page('resource.edit');
  }

  public function processEdit() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if contact has permission to the route
    if(!$this->rbac->hasPermission('Can edit contacts')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get contact id param
    $contact_id = $this->app->route->values->contact_id ?? null;
    if(empty($contact_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch contact
    $contact = $this->model->contact->fetchContact(['id' => $contact_id]);
    if(empty($contact)) {
      return $this->app->response->redirect('404');
    }

    // get and validate post data
    $validate = $this->validateResourcePostData($this->model->contact->form, 'edit');
    if(!$validate->success) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Validation failed. Errors are {".json_encode($validate->errors)."}"]);
      $this->app->flash->set($validate->errors, 'error', '/contacts/'.$contact->id.'/edit');
      return;
    }

    // data
    $data = (object) $validate->data;

    // delete session post data and cancel edition
    if($data->submit == 'cancel') {
      $this->deleteResourceSessionPostData();
      return $this->app->response->redirect("/contacts/{$contact->id}/view");
    }

    // update
    $this->model->contact->updateContact(
      [
        'surname' => $data->surname,
        'other_names' => $data->other_names,
        'email' => $data->email,
        'phone' => $data->phone,
        'address' => $data->address,
        'comments' => $data->comments
      ],
      ['id' => $contact->id]
    );

    // delete resource session post data
    $this->deleteResourceSessionPostData();

    // redirect to updated contact
    $this->app->flash->set("Contact '{$contact->surname}' updated successfully", 'success', '/contacts/'.$contact->id.'/view');
    return;
  }

  public function delete() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if contact has permission to the route
    if(!$this->rbac->hasPermission('Can delete contacts')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get contact id param
    $contact_id = $this->app->route->values->contact_id ?? null;
    if(empty($contact_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch contact
    $contact = $this->model->contact->fetchContact(['id' => $contact_id]);
    if(empty($contact)) {
      return $this->app->response->redirect('404');
    }

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', "Delete Contact '{$contact->surname}'");

    // set page title
    $this->view->setVar('pagetitle', ['Delete Contact '.$contact->surname, $this->basePageTitle]);

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'contacts','label'=>'Contacts','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'contacts/'.$contact->id.'/view','label'=>$contact->surname,'type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'Delete']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // form
    $form = $this->getDeleteForm($this->model->contact->form, 'contacts/'.$contact->id.'/delete');
    $this->view->setVar('form', $form);
    $this->view->setVar('resource_type', $this->resource_type);

    // return form view
    $this->view->setVar('resource', $contact);
    $this->view->setVar('columns', $this->model->contact->listing);
    return $this->view->page('resource.delete');
  }

  public function processDelete() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if contact has permission to the route
    if(!$this->rbac->hasPermission('Can delete contacts')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get contact id param
    $contact_id = $this->app->route->values->contact_id ?? null;
    if(empty($contact_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch contact
    $contact = $this->model->contact->fetchContact(['id' => $contact_id]);
    if(empty($contact)) {
      return $this->app->response->redirect('404');
    }

    // get post data
    $post = $this->app->request->getPostParams();
    $post = (object) $this->expectedOnly(['submit'], $post);

    // cancel form
    if(isset($post->submit) && $post->submit != 'delete') {
      $this->app->response->redirect("/contacts/{$contact->id}/view");
      return $this->app->response;
    }

    // delete
    $delete_contact = $this->model->contact->deleteContact(['id' => $contact->id],['limit' => 1]);
    if($delete_contact === false) {
      $this->app->flash->set("Server error while deleting contact '{$contact->surname}'", 'error', "/contacts/{$contact->id}/view");
      return;
    }

    // delete group contact mapping
    $this->model->contact->deleteContactGroups($contact->id);

    // redirect to deleted contact
    $this->app->flash->set("Contact '{$contact->surname}' deleted successfully", 'success', '/contacts');
    return;
  }

  public function enable() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if contact has permission to the route
    if(!$this->rbac->hasPermission('Can enable contacts')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get contact id param
    $contact_id = $this->app->route->values->contact_id ?? null;
    if(empty($contact_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch contact
    $contact = $this->model->contact->fetchContact(['id' => $contact_id]);
    if(empty($contact)) {
      return $this->app->response->redirect('404');
    }

    // check if contact is already enabled
    if($contact->status == 'Active') {
      $this->app->flash->set("Contact '{$contact->surname}' is already enabled", 'error', '/contacts/'.$contact->id.'/view');
    }

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', "Enable Contact '{$contact->surname}'");

    // set page title
    $this->view->setVar('pagetitle', ['Enable Contact '.$contact->surname, $this->basePageTitle]);

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'contacts','label'=>'Contacts','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'contacts/'.$contact->id.'/view','label'=>$contact->surname,'type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'Enable']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // form
    $form = $this->getEnableForm($this->model->contact->form, 'contacts/'.$contact->id.'/enable');
    $this->view->setVar('form', $form);
    $this->view->setVar('resource_type', $this->resource_type);

    // return form view
    $this->view->setVar('resource', $contact);
    $this->view->setVar('columns', $this->model->contact->listing);
    return $this->view->page('resource.enable');
  }

  public function processEnable() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if contact has permission to the route
    if(!$this->rbac->hasPermission('Can enable contacts')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get contact id param
    $contact_id = $this->app->route->values->contact_id ?? null;
    if(empty($contact_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch contact
    $contact = $this->model->contact->fetchContact(['id' => $contact_id]);
    if(empty($contact)) {
      return $this->app->response->redirect('404');
    }

    // check if contact is already enabled
    if($contact->status == 'Active') {
      $this->app->flash->set("Contact '{$contact->surname}' is already enabled", 'error', '/contacts/'.$contact->id.'/view');
    }

    // get post data
    $post = $this->app->request->getPostParams();
    $post = (object) $this->expectedOnly(['submit'], $post);

    // cancel form
    if(isset($post->submit) && $post->submit != 'enable') {
      $this->app->response->redirect("/contacts/{$contact->id}/view");
      return $this->app->response;
    }

    // process enable
    $enable = $this->model->contact->enableContact(['id' => $contact->id]);
    if($enable === false) {
      $this->app->flash->set("Server error while activating contact '{$contact->surname}'", 'error', "/contacts/{$contact->id}/view");
      return;
    }

    // redirect to contact
    $this->app->flash->set("Contact '{$contact->surname}' enabled successfully", 'success', "/contacts/{$contact->id}/view");
    return;
  }

  public function disable() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if contact has permission to the route
    if(!$this->rbac->hasPermission('Can disable contacts')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get contact id param
    $contact_id = $this->app->route->values->contact_id ?? null;
    if(empty($contact_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch contact
    $contact = $this->model->contact->fetchContact(['id' => $contact_id]);
    if(empty($contact)) {
      return $this->app->response->redirect('404');
    }

    // check if contact is already enabled
    if($contact->status == 'Inactive') {
      $this->app->flash->set("Contact '{$contact->surname}' is already disabled", 'error', '/contacts/'.$contact->id.'/view');
    }

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', "Disable Contact '{$contact->surname}'");

    // set page title
    $this->view->setVar('pagetitle', ['Disable Contact '.$contact->surname, $this->basePageTitle]);

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'contacts','label'=>'Contacts','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'contacts/'.$contact->id.'/view','label'=>$contact->surname,'type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'Disable']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // form
    $form = $this->getDisableForm($this->model->contact->form, 'contacts/'.$contact->id.'/disable');
    $this->view->setVar('form', $form);
    $this->view->setVar('resource_type', $this->resource_type);

    // return form view
    $this->view->setVar('resource', $contact);
    $this->view->setVar('columns', $this->model->contact->listing);
    return $this->view->page('resource.disable');
  }

  public function processDisable() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if contact has permission to the route
    if(!$this->rbac->hasPermission('Can disable contacts')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get contact id param
    $contact_id = $this->app->route->values->contact_id ?? null;
    if(empty($contact_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch contact
    $contact = $this->model->contact->fetchContact(['id' => $contact_id]);
    if(empty($contact)) {
      return $this->app->response->redirect('404');
    }

    // check if contact is already enabled
    if($contact->status == 'Inactive') {
      $this->app->flash->set("Contact '{$contact->surname}' is already disabled", 'error', '/contacts/'.$contact->id.'/view');
    }

    // get post data
    $post = $this->app->request->getPostParams();
    $post = (object) $this->expectedOnly(['submit'], $post);

    // cancel form
    if(isset($post->submit) && $post->submit != 'disable') {
      $this->app->response->redirect("/contacts/{$contact->id}/view");
      return $this->app->response;
    }

    // disable
    $disable = $this->model->contact->disableContact(['id' => $contact->id]);
    if($disable === false) {
      $this->app->flash->set("Server error while deactivating contact '{$contact->surname}'", 'error', "/contacts/{$contact->id}/view");
      return;
    }

    // redirect to contact
    $this->app->flash->set("Contact '{$contact->surname}' disabled successfully", 'success', "/contacts/{$contact->id}/view");
    return;
  }

  public function groups() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if contact has permission to the route
    if(!$this->rbac->hasPermission(['Can manage contact groups'])) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get contact id param
    $contact_id = $this->app->route->values->contact_id ?? null;
    if(empty($contact_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch contact
    $contact = $this->model->contact->fetchContact(['id' => $contact_id]);
    if(empty($contact)) {
      return $this->app->response->redirect('404');
    }

    // fetch contact groups
    $contact_groups = $this->model->contact->loadContactGroups($contact->id);
    $contact->groups = [];
    foreach($contact_groups as $rp) {
      $contact->groups[] = $rp->id;
    }
    $this->view->setVar('contact', $contact);

    // fetch all groups
    $this->model->group = new Group($this->app);
    $groups = $this->model->group->fetchGroups([], ['perPage' => 'all']);
    $formatted_groups = [];
    foreach($groups->rows as $r) {
      $formatted_groups[$r->id] = $r;
    }
    $this->view->setVar('groups', json_decode(json_encode($formatted_groups)));

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', "Manage Groups for Contact '{$contact->surname}'");

    // set page title
    $this->view->setVar('pagetitle', ['Manage Groups for Contact '.$contact->surname, $this->basePageTitle]);

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'contacts','label'=>'Contacts','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'contacts/'.$contact->id.'/view','label'=>$contact->surname,'type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'Manage Groups']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // view
    return $this->view->page('contacts.groups');
  }

  public function processGroups() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if contact has permission to the route
    if(!$this->rbac->hasPermission(['Can manage contact groups'])) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get contact id param
    $contact_id = $this->app->route->values->contact_id ?? null;
    if(empty($contact_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch contact
    $contact = $this->model->contact->fetchContact(['id' => $contact_id]);
    if(empty($contact)) {
      return $this->app->response->redirect('404');
    }

    // fetch contact groups
    $groups = $this->model->contact->loadContactGroups($contact->id);
    $group_ids = [];
    foreach($groups as $r) {
      $group_ids[] = $r->id;
    }

    // post data
    $post = $this->app->request->getPostParams();
    $post = (object) $this->expectedOnly(['group'], $post);

    // save groups
    $this->model->contact->saveContactGroups($contact->id, $this->loggedInUser->id, $post->group, array_diff($post->group, $group_ids));

    // redirect to contact
    $this->app->flash->set("Groups for Contact '{$contact->surname}' updated successfully", 'success', '/contacts/'.$contact->id.'/groups');
    return;
  }

  public function sendSms() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if contact has permission to the route
    if(!$this->rbac->hasPermission('Can message a contact')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get contact id param
    $contact_id = $this->app->route->values->contact_id ?? null;
    if(empty($contact_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch contact
    $contact = $this->model->contact->fetchContact(['id' => $contact_id]);
    if(empty($contact)) {
      return $this->app->response->redirect('404');
    }

    // check if contact is active
    if($contact->status == 'Inactive') {
      $this->app->flash->set("Contact '{$contact->surname}' is inactive", 'error', '/contacts/'.$contact->id.'/view');
    }

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', "Send SMS to Contact '{$contact->surname}'");

    // set page title
    $this->view->setVar('pagetitle', ['Send SMS to Contact '.$contact->surname, $this->basePageTitle]);

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'contacts','label'=>'Contacts','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'contacts/'.$contact->id.'/view','label'=>$contact->surname,'type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'Send SMS']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // form
    $form = $this->getSmsForm($this->model->contact->form, 'contacts/'.$contact->id.'/send-sms');
    $this->view->setVar('form', $form);
    $this->view->setVar('resource_type', $this->resource_type);

    // return form view
    $this->view->setVar('resource', $contact);
    $this->view->setVar('columns', $this->model->contact->listing);
    return $this->view->page('messaging.sms');
  }

  public function processSendSms() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if contact has permission to the route
    if(!$this->rbac->hasPermission('Can message a contact')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get contact id param
    $contact_id = $this->app->route->values->contact_id ?? null;
    if(empty($contact_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch contact
    $contact = $this->model->contact->fetchContact(['id' => $contact_id]);
    if(empty($contact)) {
      return $this->app->response->redirect('404');
    }

    // check if contact is already enabled
    if($contact->status == 'Inactive') {
      $this->app->flash->set("Contact '{$contact->surname}' is disabled", 'error', '/contacts/'.$contact->id.'/view');
    }

    // get post data
    $expected = ['message','submit'];
    $post = $this->app->request->getPostParams();
    $post = $this->expectedOnly($expected, $post);

    // save to session
    $this->app->session->set('post', [$this->resource_type => (object) ['message' => $post['message']]]);

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

      // delete session post data and cancel edition
      if($post['submit'] != 'sms') {
        $this->deleteResourceSessionPostData();
        return $this->app->response->redirect('contacts/'.$contact->id.'/view');
      }

      // save in database - $subject, $message, $contact_ids, $group_id = null, $created_by = 0
      $this->model->messaging = new Messaging($this->app);
      $insert = $this->model->messaging->newMessaging(null, $data->message, [$contact->id], null,$this->loggedInUser->id);
      if(!$insert) {
        $this->app->flash->set('Server error while sending SMS', 'error', '/contacts');
        return;
      }

      // TODO send actual SMS

      // delete resource session post data
      $this->deleteResourceSessionPostData();

      // redirect to created contact
      $this->app->flash->set("SMS to '{$contact->surname}' sent successfully", 'success', '/contacts/'.$contact->id.'/view');
      return;

    } else {
      // redirect back with validation errors
      $this->app->log->error(['ResponseCode'=>400,'TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"validation failed: ".json_encode($validate->errors)]);
      $this->app->flash->set($validate->errors, 'error', '/contacts/'.$contact->id.'/send-sms');
    }
  }

  public function sendEmail() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if contact has permission to the route
    if(!$this->rbac->hasPermission('Can message a contact')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get contact id param
    $contact_id = $this->app->route->values->contact_id ?? null;
    if(empty($contact_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch contact
    $contact = $this->model->contact->fetchContact(['id' => $contact_id]);
    if(empty($contact)) {
      return $this->app->response->redirect('404');
    }

    // check if contact is active
    if($contact->status == 'Inactive') {
      $this->app->flash->set("Contact '{$contact->surname}' is inactive", 'error', '/contacts/'.$contact->id.'/view');
    }

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', "Send Email to Contact '{$contact->surname}'");

    // set page title
    $this->view->setVar('pagetitle', ['Send Email to Contact '.$contact->surname, $this->basePageTitle]);

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'contacts','label'=>'Contacts','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'contacts/'.$contact->id.'/view','label'=>$contact->surname,'type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'Send Email']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // form
    $form = $this->getEmailForm($this->model->contact->form, 'contacts/'.$contact->id.'/send-email');
    $this->view->setVar('form', $form);
    $this->view->setVar('resource_type', $this->resource_type);

    // return form view
    $this->view->setVar('resource', $contact);
    $this->view->setVar('columns', $this->model->contact->listing);
    return $this->view->page('messaging.email');
  }

  public function processSendEmail() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if contact has permission to the route
    if(!$this->rbac->hasPermission('Can message a contact')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get contact id param
    $contact_id = $this->app->route->values->contact_id ?? null;
    if(empty($contact_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch contact
    $contact = $this->model->contact->fetchContact(['id' => $contact_id]);
    if(empty($contact)) {
      return $this->app->response->redirect('404');
    }

    // check if contact is already enabled
    if($contact->status == 'Inactive') {
      $this->app->flash->set("Contact '{$contact->surname}' is disabled", 'error', '/contacts/'.$contact->id.'/view');
    }

    // get post data
    $expected = ['subject','message','submit'];
    $post = $this->app->request->getPostParams();
    $post = $this->expectedOnly($expected, $post);

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

      // delete session post data and cancel edition
      if($post['submit'] != 'email') {
        $this->deleteResourceSessionPostData();
        return $this->app->response->redirect('contacts/'.$contact->id.'/view');
      }

      // save in database
      $this->model->messaging = new Messaging($this->app);
      $insert = $this->model->messaging->newMessaging($data->subject, $data->message, [$contact->id], null,$this->loggedInUser->id);
      if(!$insert) {
        $this->app->flash->set('Server error while sending Email', 'error', '/contacts');
        return;
      }

      // TODO send actual Email

      // delete resource session post data
      $this->deleteResourceSessionPostData();

      // redirect to created contact
      $this->app->flash->set("Email to '{$contact->surname}' sent successfully", 'success', '/contacts/'.$contact->id.'/view');
      return;

    } else {
      // redirect back with validation errors
      $this->app->log->error(['ResponseCode'=>400,'TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"validation failed: ".json_encode($validate->errors)]);
      $this->app->flash->set($validate->errors, 'error', '/contacts/'.$contact->id.'/send-email');
    }
  }

}
