<?php

namespace SMVC\Core;

use SMVC\Exceptions\SMVCException;
use SMVC\Helpers\File;
use SMVC\Helpers\Minify;
use SMVC\Helpers\Utils;

class View {

  protected $app;

  protected $layout;
  protected $partial;
  protected $page;
  public $vars;
  protected $is_logged_in = false;

  public function __construct($app) {
    $this->app = $app;
    $this->vars = new \stdClass();
    $this->layout = 'main';
  }

  public function page($pagename) {

    // base url
    $this->setVar('base_url', $this->app->response->getBaseUrl());

    // dashboard icons
    $icons = $this->loadIcons();
    $this->setVar('icons', $icons);

    // select config that can be accessible on the view
    $this->loadConfig();

    // active route can be accessible on the view
    $this->setVar('route', $this->app->route);

    // is logged in
    $this->setVar('isLoggedIn', $this->is_logged_in);

    // module
    $this->setVar('module', $this->vars->module ?? null);

    // get page title
    $this->setVar('pagetitle', $this->getPageTitle());

    // get breadcrumbs
    $this->getBreadcrumbs();

    // load flash messages
    $this->loadFlashMessage();

    // load old post vars
    $this->loadPostVars();

    // output vars
    $vars = $this->vars;
    $vars = compact('vars');
    $vars = array_shift($vars);

    // if pagename has dots, replace with DS
    $pagename = str_replace('.', DS, $pagename);

    // load page
    ob_start();
    $file = VIEWS.'pages'.DS.$pagename.'.php';
    if(File::exists($file)) {
      include($file);
    } else {
      throw new SMVCException(__FILE__, __LINE__, "File '{$file}' doesn't exist");
    }
    $page = ob_get_clean();

    // load partial
   ob_start();
    $partial = VIEWS.'partials'.DS.$this->partial.'.partial.php';
    if(File::exists($partial)) {
      include($partial);
    } else {
      throw new SMVCException(__FILE__, __LINE__, "File '{$partial}' doesn't exist");
    }
    $content = ob_get_clean();

    // load layout
    ob_start();
    $layout = VIEWS.'layouts'.DS.$this->layout.'.layout.php';
    if(File::exists($layout)) {
      include($layout);
    } else {
      throw new SMVCException(__FILE__, __LINE__, "File '{$layout}' doesn't exist");
    }
    $output = ob_get_clean();

    // compress html
    $output = Minify::html($output);

    // set content
    $this->app->response->setContent($output);
    return;
  }

  public function setPartial($partial) {
    $this->partial = $partial;
  }

  public function setVar($name, $value = null) {
    $this->vars->{$name} = $value;
  }

  public function setIsLoggedIn($is_logged_in = false) {
    $this->is_logged_in = $is_logged_in;
  }

  public function setSampleBulkFiles() {
    $sample_uploads = [
      [
        'link' => 'assets/samples/bulk-csv.csv',
        'type' => 'CSV',
        'image' => 'assets/filetypes/csv.png',
        'alt' => 'CSV file type'
      ],
      [
        'link' => 'assets/samples/bulk-xls.xls',
        'type' => 'Excel (XLS)',
        'image' => 'assets/filetypes/xls.png',
        'alt' => 'XLS file type'
      ],
      [
        'link' => 'assets/samples/bulk-xlsx.xlsx',
        'type' => 'Excel (XLSX)',
        'image' => 'assets/filetypes/xlsx.png',
        'alt' => 'XLSX file type'
      ],
    ];
    $this->setVar('sample_uploads', json_decode(json_encode($sample_uploads)));
  }

  public function loadIcons() {
    $icons = [

      // menu icons
      'dashboard' => 'dashboard-black-18dp.svg',
      'contacts' => 'people_alt_black_24dp.svg',
      'manage' => 'settings-black-18dp.svg',
      'messaging' => 'chat_bubble_black_24dp.svg',

      // dropdown icons
      'dropdown' => 'keyboard_arrow_right-black-18dp.svg',

      // profile menu
      'profile' => 'person-black-18dp.svg',
      'logout' => 'forward-black-18dp.svg',

      // resource actions
      'view' => 'visibility_black_24dp.svg',
      'edit' => 'edit_black_24dp.svg',
      'download' => 'download_black_24dp.svg',
      'delete' => 'delete_black_24dp.svg',
      'enable' => 'check_box_black_24dp.svg',
      'disable' => 'disabled_by_default_black_24dp.svg',
      'close-user' => 'person_off_black_24dp.svg',

      // manage
      'manage-users' => 'group_black_24dp.svg',
      'manage-roles' => 'folder_black_24dp.svg',
      'manage-permissions' => 'vpn_key_black_24dp.svg',
      'manage-groups' => 'folder_black_24dp.svg',
      'manage-contacts' => 'group_black_24dp.svg',

      // messaging
      'send-sms' => 'chat_black_24dp.svg',
      'send-email' => 'email_black_24dp.svg',
    ];

    $output = new \stdClass;
    foreach($icons as $name => $file) {
      $path = PUBLIC_FOLDER.'assets'.DS.'icons'.DS.'material-icons'.DS.$file;
      $output->{$name} = file_get_contents($path);
    }

    return $output;
  }

  private function getPageTitle() {
    $app_name = $this->app->config->app->name;
    $app_sep = $this->app->config->app->title_appname_separator;
    $page_sep = $this->app->config->app->title_subpage_separator;

    // page title
    $title = '';

    // build page title
    if(isset($this->vars->pagetitle) && !empty($this->vars->pagetitle)) {
      if(is_array($this->vars->pagetitle) || is_object($this->vars->pagetitle)) {
        $title .= implode(" {$page_sep} ", (array) $this->vars->pagetitle);
      } else {
        $title .= $this->vars->pagetitle;
      }
      $title = trim($title).' '.trim($app_sep).' ';
    }

    // add app name
    return $title.trim($app_name);
  }

  private function loadConfig() {

    // only load the needed configs to the view
    $c = $this->app->config;
    $config = [
      'app' => [
        'company' => $c->app->company,
        'name' => $c->app->name,
        'lang' => $c->app->lang
      ]
    ];

    $this->setVar('config', json_decode(json_encode($config)));
  }

  private function getBreadcrumbs() {
    $bc = '';

    if(isset($this->vars->breadcrumbs) && !empty($this->vars->breadcrumbs)):
      $bc .= '<div class="breadcrumbs"><ul>';
      foreach($this->vars->breadcrumbs as $breadcrumb):
        if($breadcrumb->isActive) {
          $bc .= '<li class="active-breadcrumb"><span>'.$breadcrumb->label.'</span></li>';
        } else {
          if($breadcrumb->type == 'form') {
            $fields = '';
            foreach($breadcrumb->fields as $k => $v) {
              $fields .= '<input type="hidden" name="'.$k.'" id="'.$k.'" value="'.$v.'">';
            }
            $bc .= '<li><form action="'.$breadcrumb->action.'" method="'.$breadcrumb->method.'">'.$fields.'<button name="submit" id="submit" value="'.$breadcrumb->value.'">'.$breadcrumb->label.'</button></form></li>';
          } elseif ($breadcrumb->type == 'link') {
            $bc .= '<li><a href="'.$breadcrumb->href.'">'.$breadcrumb->label.'</a></li>';
          }
        }
      endforeach;
      $bc .= '</ul></div>';
    endif;

    $this->setVar('breadcrumbs', $bc);
  }

  private function loadFlashMessage() {
    $alert = null;

    $flash = $this->app->flash->displayFlash();
    if($flash) {
      $alert = '<ul class="flash flash-'.$flash->class.'">';
      foreach($flash->message as $msg) {
        $alert .= '<li>'.$msg.'</li>';
      }
      $alert .= '</ul>';
    }
    $this->setVar('flash', $alert);
  }

  private function loadPostVars() {
    if($this->app->session->has('post')) {
      $post = $this->app->session->get('post');
      $this->setVar('post', json_decode(json_encode($post)));
    }
  }

}
