<?php

namespace SMVC\Controller;

use SMVC\Core\Controller;
use SMVC\Model\Upload;
use SMVC\Model\Contact;

use SMVC\Helpers\Pdf;
use SMVC\Helpers\Pager;
use SMVC\Helpers\File;

class UploadController extends Controller {

  public function __construct($app) {
    parent::__construct($app);

    // set partial to use as main
    $this->view->setPartial('main');

    // set base page title
    $this->basePageTitle = 'Bulk Uploads';

    // active menu
    $this->view->setVar('active', 'uploads');

    // upload model
    $this->model->upload = new Upload($app);
    $this->view->setVar('module', $this->model->upload->module);

    // resource name
    $this->resource_name = 'uploads';
    $this->resource_type = 'upload';
    $this->view->setVar('resource_name', $this->resource_name);
  }

  public function listing() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if upload has permission to the route
    if(!$this->rbac->hasPermission('Can view uploads')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // load options
    $options = $this->loadOptions($this->model->upload->listing);

    // reset
    if(isset($options['submit']) && $options['submit'] == 'reset') {
      $this->app->response->redirect('/uploads');
    }

    // fetch uploads
    $uploads = $this->model->upload->fetchUploads([], $options);

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', 'Bulk Uploads');

    // filter form
    $filter_form = $this->getResourceFilterform('uploads', $this->model->upload->listing, $options);
    $this->view->setVar('filter_form', $filter_form);

    // links
    $links = $this->getLinksMarkup($this->model->upload->links, $uploads->rows);
    foreach($links as $name => $markup) {
      if(!empty($markup)) {
        $this->view->setVar($name, $markup);
      }
    }

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'Bulk Uploads']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // pager
    $pager = Pager::paginate(
      $uploads->meta->count,
      $uploads->meta->page,
      $uploads->meta->pages,
      'uploads/',
      $this->app->request->getGetParamsAsUri()
    );
    $this->view->setVar('pager', $pager);

    // set page title
    $this->view->setVar('pagetitle', [$this->basePageTitle]);

    // return form view
    $this->view->setVar('records', $uploads->rows);
    $this->view->setVar('columns', $this->model->upload->listing);
    return $this->view->page('resource.listing');
  }

  public function view() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if upload has permission to the route
    if(!$this->rbac->hasPermission('Can view uploads')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get upload id param
    $upload_id = $this->app->route->values->upload_id ?? null;
    if(empty($upload_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch upload
    $upload = $this->model->upload->fetchUpload(['id' => $upload_id]);
    if(!isset($upload->id)) {
      return $this->app->response->redirect('404');
    }

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', $upload->original_name);

    // links
    $links = $this->getLinksMarkup($this->model->upload->links, [$upload]);
    foreach($links as $name => $markup) {
      if(!empty($markup)) {
        $this->view->setVar($name, $markup);
      }
    }

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'uploads','label'=>'Bulk Uploads','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>$upload->original_name]);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // set page title
    $this->view->setVar('pagetitle', [$upload->original_name, $this->basePageTitle]);

    // return detail view
    $this->view->setVar('resource', $upload);
    $this->view->setVar('columns', !empty($this->model->upload->detail) ? $this->model->upload->detail : $this->model->upload->listing);
    return $this->view->page('resource.detail');
  }

  public function new() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if upload has permission to the route
    if(!$this->rbac->hasPermission('Can bulk upload contacts')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // set page header (shown on the top of the page)
    $this->view->setVar('pageheader', 'Upload Contacts');

    // set page title
    $this->view->setVar('pagetitle', ['Upload Contacts', $this->basePageTitle]);

    // breadcrumb
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['href'=>'uploads','label'=>'Uploads','type'=>'link']);
    $this->breadcrumbs[] = $this->setBreadcrumbEntry(['isActive'=>true,'label'=>'Upload Contacts']);
    $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));

    // sample bulk files
    $this->view->setSampleBulkFiles();

    // form
    $form = $this->getUploadForm('uploads/new');
    $this->view->setVar('form', $form);

    // return form view
    return $this->view->page('resource.upload');
  }

  public function processNew() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if upload has permission to the route
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
      return $this->app->response->redirect("/uploads");
    }

    // validate uploaded file
    $file = $this->verifyAndUploadBulkFile();

    //comments
    $comments = $post['comments'] ?? null;

    // save file details to database, before processing file
    $upload_id = $this->model->upload->newUpload($file, $comments, null, $this->loggedInUser->id);

    // read file and process uploads
    $uploads = $this->readBulkFile($file['new_file_name'], $file['extension']);

    // TODO we assume all uploads are valid

    // save uploads in database
    $this->model->contact = new Contact($this->app);
    $inserted = $this->model->contact->bulkInsert($uploads, $upload_id, $this->loggedInUser->id);

    if($inserted) {

      // delete resource session post data
      $this->deleteResourceSessionPostData();

      // redirect to uploads
      $this->app->flash->set("Bulk uploads created successfully", 'success', '/uploads');
      return;
    } else {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Bulk insertion failed"]);
      $this->app->flash->set(['A server error occured while processing the bulk insertion','Kindly try again later'], 'error', '/uploads/upload');
      return;
    }
  }

  public function download() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if upload has permission to the route
    if(!$this->rbac->hasPermission('Can download uploaded files')) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // get upload id param
    $upload_id = $this->app->route->values->upload_id ?? null;
    if(empty($upload_id)) {
      return $this->app->response->redirect('404');
    }

    // try to fetch upload
    $upload = $this->model->upload->fetchUpload(['id' => $upload_id]);
    if(!isset($upload->id)) {
      return $this->app->response->redirect('404');
    }

    File::download(UPLOAD.$upload->new_name, $upload->original_name);
    exit;
  }

  public function export() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // check if upload has permission to the route
    if(!$this->rbac->hasPermission(['Can export uploads'])) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>'RBAC','Action'=>'PERMISSION DENIED','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Permission denied for {$this->loggedInUser->username} while trying to access route {$this->app->route->name}"]);
      return $this->app->response->redirect('/401');
    }

    // fetch uploads
    $uploads = $this->model->upload->fetchUploads([], ["perPage" => "all"]);

    // export
    $pdf = new Pdf();
    return $pdf->exportListing(
      'Uploads',
      $this->model->upload->listing,
      $uploads->rows
    );
  }

}
