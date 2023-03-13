<?php

namespace SMVC\Core;

use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLS;

use SMVC\Core\Rbac;
use SMVC\Core\View;

use SMVC\Model\User;
use SMVC\Model\Session;

use SMVC\Helpers\Text;
use SMVC\Helpers\File;
use SMVC\Helpers\Date;
use SMVC\Helpers\Validate;
use SMVC\Helpers\Api;
use SMVC\Helpers\Dot;

class Controller {

  protected $app;
  protected $loggedInUser;
  protected $rbac;
  protected $view;
  protected $model;
  protected $resource_name = null;
  protected $resource_type = null;
  protected $basePageTitle = null;
  protected $breadcrumbs = [];
  protected $perPageOptions = [];
  protected $orderDirOptions = [];
  protected $is_logged_in = false;
  protected $encrypt_sep = '##';

  public function __construct($app) {
    $this->app = $app;

    $this->view = new View($app);
    $this->rbac = new Rbac($app);

    $this->model = new \stdClass;
    $this->model->user = new User($app);
    $this->model->session = new Session($app);

    // asset version (before checking if user is logged in)
    $this->view->setVar('asset_version', $this->app->config->app->asset_version);

    // check if user is logged in
    $this->checkIfUserIsLoggedIn();

    // is protected route? redirect to login
    if($this->app->route->route->isProtected && !$this->is_logged_in) {
      $this->app->flash->set('Kindly login first', 'error', '/');
      return;
    }

    // set dashboard breadcrumb
    if(!in_array($this->app->route->name, ['dashboard','e401','e404','e500'])) {
      $this->breadcrumbs[] = $this->setBreadcrumbEntry([
        'type' => 'link',
        'href' => 'dashboard',
        'label' => 'Dashboard'
      ]);
      $this->view->setVar('breadcrumbs', json_decode(json_encode($this->breadcrumbs)));
    }

    // set is logged in variable
    $this->view->setIsLoggedIn($this->is_logged_in);

    // per page options
    $this->perPageOptions = [10, 20, 50, 100, 250, 500];

    // order dir options
    $this->orderDirOptions = [
      'asc' => 'Ascending',
      'desc' => 'Descending',
    ];
  }

  private function checkIfUserIsLoggedIn() {

    $session_name = $this->app->config->security->logged_in_session_name;
    if(!$this->app->session->has($session_name)) {
      return;
    }

    // fetch session from database
    $session = $this->model->session->getActiveSession();
    if(!$session) {
      return;
    }

    // update last seen
    $this->model->session->updateLastSeen($session->id);

    // load user
    $user = $this->model->user->fetchUser(['id' => $session->user_id]);
    if(!$user) {
      return;
    }

    // avatarize
    $user->initials = Text::avatarize($user->username);

    // rbac permissions
    $this->rbac->loadPermissions($session->user_id);
    $this->view->setVar('user_permissions', $this->rbac->permissions->names);

    // set profile vars
    $this->view->setVar('profile', $user);

    // set is logged in
    if($session->status == 'Active') {
      $this->is_logged_in = true;
    }

    // add user to app
    $this->loggedInUser = $user;
  }

  protected function generateUserSessionId($length = 32) {
    return $this->generateToken($length);
  }

  // https://www.php.net/manual/en/function.random-bytes.php
  private function generateToken($length = 32) {

    // minimum length is 8
    if(!isset($length) || intval($length) <= 8 ){
      $length = 32;
    }

    if(function_exists('random_bytes')) {
        return bin2hex(random_bytes($length));
    }

    if(function_exists('mcrypt_create_iv')) {
        return bin2hex(mcrypt_create_iv($length, MCRYPT_DEV_URANDOM));
    }

    if(function_exists('openssl_random_pseudo_bytes')) {
        return bin2hex(openssl_random_pseudo_bytes($length));
    }

    // last option, return random string
    return Text::randomString('alnum', $length);
  }

  protected function expectedOnly($expected = [], $vars = []) {

    if(empty($vars)) {
      return [];
    }

    $clean = [];
    foreach($vars as $key => $value) {
      if(in_array($key, $expected)) {
        $clean[$key] = $value;
      }
    }

    return $clean;
  }

  protected function loadOptions($columns) {

    $options = [];
    $route = $this->app->route->values;
    $get = (object) $this->app->request->getGetParams();

    // search
    if(isset($get->search)) {
      $options['search'] = $get->search;
    }

    // page
    if(isset($route->page)) {
      $options['page'] = $route->page;
    }

    // perpage
    if(isset($get->perpage)) {
      if(in_array($get->perpage, $this->perPageOptions)) {
        $options['perPage'] = $get->perpage;
      }
    } else {
      $options['perPage'] = 50;
    }

    // orderby
    if(isset($get->orderby)) {
      if(in_array($get->orderby, array_keys($columns))) {
        $options['orderBy'] = $get->orderby;
      }
    }

    // orderdir
    if(isset($get->orderdir)) {
      if(in_array($get->orderdir, array_keys($this->orderDirOptions))) {
        $options['orderDir'] = $get->orderdir;
      }
    }

    // submit
    if(isset($get->submit)) {
      $options['submit'] = $get->submit;
    }

    return $options;
  }

  protected function getResourceFilterform($url, $columns, $options = []) {

    // trim / from url
    $url = trim($url, '/');

    // create form
    $html = '<form method="GET" action="'.$url.'">';
    $html .= '<div><div>';
    $html .= '<label for="search">Filter</label>';

    $v = (isset($options['search']) && !empty($options['search'])) ? $options['search'] : null;
    $html .= '<input type="text" name="search" id="search" placeholder="Search..." value="'.$v.'">';

    $html .= '</div><div>';
    $html .= '<label for="orderby">Order By</label><p>';
    $html .= '<select name="orderby" id="orderby" title="Order By">';

    foreach($columns as $ck => $cv) {
      $s = (isset($options['orderBy']) && $ck == $options['orderBy']) ? ' selected="selected"' : null;
      $html .= '<option value="'.$ck.'"'.$s.'>'.$cv['label'].'</option>';
    }

    $html .= '</select><select name="orderdir" id="orderdir" title="Order Direction">';

    foreach($this->orderDirOptions as $odk => $odv) {
      $s = (isset($options['orderDir']) && $odk == $options['orderDir']) ? ' selected="selected"' : null;
      $html .= '<option value="'.$odk.'"'.$s.'>'.$odv.'</option>';
    }

    $html .= '</select></p></div><div>';
    $html .= '<label for="perpage">Per Page</label>';
    $html .= '<select name="perpage" id="perpage" title="Items per page">';

    foreach($this->perPageOptions as $pp) {

      $s = (isset($options['perPage']) && $pp == $options['perPage']) ? ' selected="selected"' : null;
      $html .= '<option value="'.$pp.'"'.$s.'>'.$pp.'</option>';
    }

    $html .= '</select></div></div><div><div>';
    $html .= '<button name="submit" id="filter" value="filter">Apply Filter</button>';
    $html .= '<button name="submit" id="reset" value="reset">Reset</button>';
    $html .= '</div></div></form>';

    return $html;
  }

  protected function getButtonsMarkup($buttons) {

    $html = '';
    foreach($buttons as $k => $b) {

      if(is_numeric($k)) {
        $html .= '<a href="'.$b['href'].'" class="button">'.$b['label'].'</a>';
      } else {
        $html .= '<ul class="'.$k.'">';
        foreach($b as $j) {
          $html .= '<li><a href="'.$j['href'].'" class="button">'.$j['label'].'</a></li>';
        }
        $html .= '</ul>';
      }
    }

    return $html;
  }

  protected function getLinksMarkup($links, $resources = []) {

    // load icons
    $icons = $this->view->loadIcons();

    $formatted = [];
    foreach($links as $group => $urls) {
      switch($group) {

        case 'header':
          $html = '';
          foreach($urls as $u) {
            if($this->rbac->hasPermission($u['permissions'])) {
              $html .= '<a href="'.$u['href'].'" title="'.$u['label'].'" class="button">'.$u['label'].'</a>';
            }
          }
          break;

        case 'listing':
          $html = [];
          if(!empty($resources) && !empty($urls)) {
            foreach($resources as $r) {
              $html[$r->id] = '';
              foreach($urls as $u) {
                if(strpos($u['href'], '{id}') !== false) {
                  $href = str_replace('{id}', $r->id, $u['href']);
                  if($this->rbac->hasPermission($u['permissions'])) {

                    if(in_array($u['name'], ['delete','edit','enable','disable','close','roles','overwrite']) && isset($r->status) && in_array($r->status, ['closed','deleted'])) {
                      $html[$r->id] .= '';
                    } else {

                      // hide enable button if enabled
                      /* if(($u['name'] == 'enable' && $r->enabled == 'enabled') || ($u['name'] == 'disable' && $r->enabled == 'disabled')) {
                        $html[$r->id] .= '';
                      } else {
                        $html[$r->id] .= '<a href="'.$href.'" title="'.$u['label'].'">'.$icons->{$u['icon']}.'</a>';
                      } */
                      $html[$r->id] .= '<a href="'.$href.'" title="'.$u['label'].'">'.$icons->{$u['icon']}.'</a>';

                    }

                  }
                }
              }
            }
          }
          break;

        case 'detail':
          $html = [];
          if(!empty($resources) && !empty($urls)) {
            foreach($resources as $r) {
              $html[$r->id] = '';
              foreach($urls as $class => $url) {
                $html[$r->id] .= '<ul class="'.$class.'">';
                foreach($url as $u) {
                  if(strpos($u['href'], '{id}') !== false) {
                    $href = str_replace('{id}', $r->id, $u['href']);
                    if($this->rbac->hasPermission($u['permissions'])) {

                      if(isset($r->status) && in_array($r->status, ['closed','deleted'])) {
                        $html[$r->id] .= '';
                      } else {

                        /* // hide enable button if enabled
                        if(($u['name'] == 'enable' && $r->enabled == 'enabled') || ($u['name'] == 'disable' && $r->enabled == 'disabled')) {
                          $html[$r->id] .= '';
                        } else {
                          $detail_icon = $detail_icon_class = null;
                          if(isset($u['icon']) && isset($icons->{$u['icon']})) {
                            $detail_icon = $icons->{$u['icon']};
                            $detail_icon_class = ' button-detail-icon';
                          }
                          $html[$r->id] .= '<li><a href="'.$href.'" title="'.$u['label'].'" class="button'.$detail_icon_class.'">'.$detail_icon.$u['label'].'</a></li>';
                        } */
                        $detail_icon = $detail_icon_class = null;
                        if(isset($u['icon']) && isset($icons->{$u['icon']})) {
                          $detail_icon = $icons->{$u['icon']};
                          $detail_icon_class = ' button-detail-icon';
                        }
                        $html[$r->id] .= '<li><a href="'.$href.'" title="'.$u['label'].'" class="button'.$detail_icon_class.'">'.$detail_icon.$u['label'].'</a></li>';

                      }

                    }
                  }
                }
                $html[$r->id] .= '</ul>';
              }
            }
          }
          break;

        default:
      }

      $formatted['links_'.$group] = $html;
    }

    return $formatted;
  }

  protected function getResourceForm($type, $action, $model_form, $values = [], $method = 'POST', $showCancelButton = true) {

    // form types allowed
    if(!in_array($type, ['add','new','edit'])) {
      return false;
    }

    // resource form
    $form = $model_form['resource'];

    // get edit / add forms if set
    if($type == 'edit' && isset($model_form['edit']) && !empty($model_form['edit'])) {
      $form = $model_form['edit'];
    }

    if($type == 'add' && isset($model_form['add']) && !empty($model_form['add'])) {
      $form = $model_form['add'];
    }

    // values to object
    $values = (object) $values;

    // previous post data
    $post = new \stdClass;
    if($this->app->session->has('post')) {
      $session = $this->app->session->get('post');
      if(isset($session[$this->resource_name])) {
        $post = $session[$this->resource_name];
      }
    }

    // create form html
    $html = '';
    $html .= '<form action="'.$action.'" method="'.$method.'">';

    // fields
    foreach($form as $f => $params) {

      // submit button
      if($f == 'submit') {
        $html .= '<div>';
        $html .= '<button name="submit" id="submit" value="'.$type.'">'.$params[$type]['label'].'</button>';
        if($showCancelButton) {
          $html .= '<button name="submit" id="cancel" value="cancel">'.$params['cancel']['label'].'</button>';
        }
        $html .= '</div>';
        break;
      }

      // field params
      $name = isset($params['name']) ? $params['name'] : $f;
      $id = isset($params['id']) ? $params['id'] : $f;

      //field value - existing or post
      $value = isset($values->{$name}) ? $values->{$name} : null;
      $value = isset($post->{$name}) ? $post->{$name} : $value;

      // field html
      $html .= '<div>';
      $html .= '<label for="'.$id.'">'.$params['label'].'</label>';

      if(isset($params['helptext']) && !empty($params['helptext'])) {
        $html .= '<span class="helptext">'.$params['helptext'].'</span>';
      }

      // placeholder
      $placeholder = null;
      if(isset($params['placeholder']) && !empty($params['placeholder'])) {
        switch($params['type']) {
          case 'text':
          case 'email':
          case 'textarea':
            $placeholder = ' placeholder="'.$params['placeholder'].'"';
            break;
          case 'select':
            $placeholder = '<option value="" selected>'.$params['placeholder'].'</option>';
            break;
          default:
            $placeholder = null;
        }
      }

      // build form
      switch($params['type']) {
        case 'text':
          $html .= '<input type="text" id="'.$id.'" name="'.$name.'"'.$placeholder.'" value="'.$value.'">';
          break;
        case 'email':
          $html .= '<input type="email" id="'.$id.'" name="'.$name.'"'.$placeholder.'" value="'.$value.'">';
          break;
        case 'textarea':
          $html .= '<textarea id="'.$id.'" name="'.$name.'"'.$placeholder.'>'.$value.'</textarea>';
          break;
        case 'select':
          $select_multiple = null;
          if(isset($params['multiple']) && $params['multiple'] == true) {
            $select_multiple = ' multiple="multiple"';
          }

          $html .= '<select id="'.$id.'" name="'.$name.'"'.$select_multiple.'>';
          $html .= $placeholder;
          foreach($params['options'] as $option) {
            $selected = null;
            if($value == $option['value']) {
              $selected = ' selected="selected"';
            }
            $html .= '<option value="'.$option['value'].'"'.$selected.'>'.$option['label'].'</option>';
          }
          $html .= '</select>';
          break;
        case 'checkboxes':
          $html .= '<div class="checkboxes-container">';
          foreach($params['options'] as $option) {
            $html .= '<label for="'.$option['value'].'">';
            $html .= '<input type="checkbox" id="'.$option['value'].'" name="'.$name.'[]" value="'.$option['value'].'">';
            $html .= $option['label'].'</label>';
          }
          $html .= '</div>';
          break;
        default:
          $html .= '';
      }

      $html .= '</div>';
    }

    $html .= '</form>';
    return $html;
  }

  protected function getDeleteForm($form, $action, $method = 'POST') {

    // resource form
    $form = $form['delete'];

    // create form html
    $html = '';
    $html .= '<form action="'.$action.'" method="'.$method.'">';
    $html .= '<div>';
    $html .= '<button name="submit" id="submit" value="delete">'.$form['submit']['delete']['label'].'</button>';
    $html .= '<button name="submit" id="cancel" value="cancel">'.$form['submit']['cancel']['label'].'</button>';
    $html .= '</div>';
    $html .= '</form>';

    return $html;
  }

  protected function getDisableForm($form, $action, $method = 'POST') {

    // resource form
    $form = $form['disable'];

    // create form html
    $html = '';
    $html .= '<form action="'.$action.'" method="'.$method.'">';
    $html .= '<div>';
    $html .= '<button name="submit" id="submit" value="disable">'.$form['submit']['disable']['label'].'</button>';
    $html .= '<button name="submit" id="cancel" value="cancel">'.$form['submit']['cancel']['label'].'</button>';
    $html .= '</div>';
    $html .= '</form>';

    return $html;
  }

  protected function getEnableForm($form, $action, $method = 'POST') {

    // resource form
    $form = $form['enable'];

    // create form html
    $html = '';
    $html .= '<form action="'.$action.'" method="'.$method.'">';
    $html .= '<div>';
    $html .= '<button name="submit" id="submit" value="enable">'.$form['submit']['enable']['label'].'</button>';
    $html .= '<button name="submit" id="cancel" value="cancel">'.$form['submit']['cancel']['label'].'</button>';
    $html .= '</div>';
    $html .= '</form>';

    return $html;
  }

  protected function getCloseForm($form, $action, $method = 'POST') {

    // resource form
    $form = $form['close'];

    // create form html
    $html = '';
    $html .= '<form action="'.$action.'" method="'.$method.'">';
    $html .= '<div>';
    $html .= '<button name="submit" id="submit" value="close">'.$form['submit']['close']['label'].'</button>';
    $html .= '<button name="submit" id="cancel" value="cancel">'.$form['submit']['cancel']['label'].'</button>';
    $html .= '</div>';
    $html .= '</form>';

    return $html;
  }

  protected function getSmsForm($form, $action, $method = 'POST') {

    // previous post data
    $post = new \stdClass;
    if($this->app->session->has('post')) {
      $session = $this->app->session->get('post');
      if(isset($session[$this->resource_type])) {
        $post = $session[$this->resource_type];
      }
    }
    $message = isset($post->message) ? $post->message : null;

    // resource form
    $form = $form['sms'];

    // create form html
    $html = '';
    $html .= '<form action="'.$action.'" method="'.$method.'">';
    $html .= '<div>';
    $html .= '<label for="message">Message (Maximum 160 characters)</label>';
    $html .= '<textarea id="message" name="message" data-limit="160" placeholder="The message to send">'.htmlentities($message).'</textarea>';
    $html .= '<p id="charcounter">160 Characters Left</p>';
    $html .= '</div>';
    $html .= '<div>';
    $html .= '<button name="submit" id="submit" value="sms">'.$form['submit']['sms']['label'].'</button>';
    $html .= '<button name="submit" id="cancel" value="cancel">'.$form['submit']['cancel']['label'].'</button>';
    $html .= '</div>';
    $html .= '</form>';

    return $html;
  }

  protected function getEmailForm($form, $action, $values = [], $method = 'POST') {

    // previous post data
    $post = new \stdClass;
    if($this->app->session->has('post')) {
      $session = $this->app->session->get('post');
      if(isset($session[$this->resource_type])) {
        $post = $session[$this->resource_type];
      }
    }
    $subject = isset($post->subject) ? $post->subject : null;
    $message = isset($post->message) ? $post->message : null;

    // resource form
    $form = $form['email'];

    // create form html
    $html = '';
    $html .= '<form action="'.$action.'" method="'.$method.'">';
    $html .= '<div>';
    $html .= '<label for="subject">Subject</label>';
    $html .= '<input type="text" id="subject" name="subject" placeholder="The email subject" value="'.htmlentities($subject).'">';
    $html .= '</div>';
    $html .= '<div>';
    $html .= '<label for="message">Message</label>';
    $html .= '<textarea id="message" name="message" placeholder="The message to send">'.htmlentities($message).'</textarea>';
    $html .= '<p id="charcounter">No Character Limit</p>';
    $html .= '</div>';
    $html .= '<div>';
    $html .= '<button name="submit" id="submit" value="email">'.$form['submit']['email']['label'].'</button>';
    $html .= '<button name="submit" id="cancel" value="cancel">'.$form['submit']['cancel']['label'].'</button>';
    $html .= '</div>';
    $html .= '</form>';

    return $html;
  }

  protected function getUploadForm($action, $method = 'POST') {

    // previous post data
    $post = new \stdClass;
    if($this->app->session->has('post')) {
      $session = $this->app->session->get('post');
      if(isset($session[$this->resource_type])) {
        $post = $session[$this->resource_type];
      }
    }
    $comments = isset($post->comments) ? $post->comments : null;

    // create form html
    $html = '';
    $html .= '<form action="'.$action.'" method="'.$method.'" enctype="multipart/form-data">';
    $html .= '<div>';
    $html .= '<label for="file">File:</label>';
    $html .= '<span class="helptext">Upload a CSV (.csv) or Excel (.xlsx or .xls) file less than 5mbs in size</span>';
    $html .= '<input type="file" id="file" name="file"/>';
    $html .= '</div>';
    $html .= '<div>';
    $html .= '<label for="comments">Comments (optional)</label>';
    $html .= '<textarea id="comments" name="comments" placeholder="Comments for this upload">'.htmlentities($comments).'</textarea>';
    $html .= '</div>';
    $html .= '<div>';
    $html .= '<button name="submit" id="submit" value="upload">Upload</button>';
    $html .= '<button name="submit" id="cancel" value="cancel">Cancel</button>';
    $html .= '</div>';
    $html .= '</form>';

    return $html;
  }

  protected function validateResourcePostData($form, $type = 'resource') {

    // resource form
    $form = $form[$type];

    // get post data
    $post = $this->app->request->getPostParams();
    $post = (object) $this->expectedOnly(array_keys($form), $post);

    // save post data to session
    $this->app->session->set('post', [$this->resource_name => (object) $post]);

    // validation rules
    $validation_rules = [];
    if($post->submit != 'cancel') {
      foreach($form as $f => $params) {
        if($f != 'submit') {
          $validation_rules[$f] = [
            'value' => isset($post->{$f}) ? $post->{$f} : null,
            'rules' => $params['validate']['rules']
          ];
        }
      }
    }

    // validate post data and return
    $validate = Validate::run($validation_rules);
    $validate->data['submit'] = $post->submit ?? 'cancel';
    return $validate;
  }

  protected function deleteResourceSessionPostData() {

    if($this->app->session->has('post')) {
      $session = $this->app->session->get('post');
      if(isset($session[$this->resource_name])) {
        unset($session[$this->resource_name]);
        if(empty($session)) {
          $this->app->session->delete('post');
        } else {
          $this->app->session->set('post', $session);
        }
      }
    }

    return;
  }

  protected function setBreadcrumbEntry($entry = []) {

    if(empty($entry)) {
      return null;
    }

    $breadcrumb = [
      'isActive' => false,
      'type' => 'form', // form or link
      'href' => null,
      'action' => null,
      'method' => 'POST',
      'value' => null,
      'label' => null,
      'fields' => []
    ];
    $breadcrumb_keys = array_keys($breadcrumb);

    foreach($entry as $k => $v) {
      if(in_array($k, $breadcrumb_keys)) {
        $breadcrumb[$k] = $v;
      }
    }

    return $breadcrumb;
  }

  protected function verifyAndUploadBulkFile() {

    // get uploaded file
    $file = $this->app->request->getFile('file') ?? null;
    if(!$file) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"No file uploaded"]);
      $this->app->flash->set(['Please select and upload a CSV or Excel file less than 5mbs in size'], 'error', '/contacts/upload');
    }

    // check extensions
    $allowed_exts = ['csv', 'xls', 'xlsx'];
    $ext = mb_strtolower(File::extension($file['name']));
    if(!in_array($ext, $allowed_exts)) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"File extension for the uploaded file not in allowed extensions: ".print_r($file, 1)]);
      $this->app->flash->set(['Please select and upload a CSV or Excel file less than 5mbs in size'], 'error', '/contacts/upload');
    }

    // check mime types
    $allowed_mimes = [
      "text/csv",
      "application/csv",
      "application/vnd.ms-excel",
      "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
      "application/wps-office.xls",
      "application/wps-office.xlsx"
    ];
    if(!in_array($file['type'], $allowed_mimes)) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"File mime for the uploaded file not in allowed mimes: ".print_r($file, 1)]);
      $this->app->flash->set(['Please select and upload a CSV or Excel file less than 5mbs in size'], 'error', '/contacts/upload');
    }

    // check file size
    if($file['size'] > 5 * 1024 *1024) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"File size of the uploaded file is greater than 5mbs: ".print_r($file, 1)]);
      $this->app->flash->set(['Please select and upload a CSV or Excel file less than 5mbs in size'], 'error', '/contacts/upload');
    }

    // all is well, upload and save file
    $rand = Text::randomString('alnum', 20, true);
    $new_file_name = $rand.'_'.time().'.'.$ext;
    move_uploaded_file($file['tmp_name'], UPLOAD.$new_file_name);

    $file['new_file_name'] = $new_file_name;
    $file['extension'] = $ext;
    return $file;
  }

  protected function readBulkFile($filename, $extension) {
    switch ($extension) {
      case 'csv':
        $rows = [];
        $open = fopen(UPLOAD.$filename, "r");
        if ($open) {
          while (($row = fgetcsv($open, 1000, ",")) !== FALSE) {
            $rows[] = $row;
          }
          fclose($open);
        }
        return $rows;
        break;
      case 'xlsx':
        $xlsx = SimpleXLSX::parse(UPLOAD.$filename);
        return $xlsx ? $xlsx->rows() : SimpleXLSX::parseError();
        break;
      case 'xls':
        $xlsx = SimpleXLS::parse(UPLOAD.$filename);
        return $xlsx ? $xlsx->rows() : SimpleXLS::parseError();
        break;
      default:
        return false;
        break;
    }
  }

  protected function sendOutSMS($message, $phone) {
    if(empty($message) || empty($phone)) {
      return false;
    }

    $headers = [];
    $body = [
      'sender' => $this->app->config->company,
      'message' => $message,
      'phone' => $phone
    ];

    return Api::sms($this->app->config->sms->url, $this->app->config->sms->method, $headers, $body);
  }

  protected function sendOutEmail($subject, $message, $email_address) {
    if(empty($message) || empty($email_address)) {
      return false;
    }

    $headers = [];
    $body = [
      'sender_name' => $this->app->config->app->company,
      'sender_email' => $this->app->config->email->sender_email,
      'subject' => $subject,
      'message' => $message,
      'email_address' => $email_address
    ];

    return Api::email($this->app->config->email->url, $this->app->config->email->method, $headers, $body);
  }

}
