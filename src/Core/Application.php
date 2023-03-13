<?php

namespace SMVC\Core;

use SMVC\Helpers\Session;
use SMVC\Helpers\Request;
use SMVC\Helpers\Response;
use SMVC\Helpers\Flash;
use SMVC\Helpers\Log;
use SMVC\Helpers\File;

class Application {

  /**
   * This variable hold all the application details and is passed around the app.
   * These details inlude the Request object, Response Object, Configs, Session,
   * Cookies, etc
   * @var Object
   */
  private $app;

  /**
   * The controller namespace
   * @var string
   */
  private $controllerNamespace = "SMVC\\Controller\\";

  /**
   * Start the application
   */
  public function __construct() {

    // the app object
    $this->app = new \stdClass();

    // load configs
    $this->loadBaseConfig();
    $this->loadEnvConfig();

    // start session and add it to $app
    $this->app->session = new Session(SESSION_PREFIX);

    // get the HTTP request and add it to the $app
    $this->app->request = new Request(
      $_GET,
      $_POST,
      $_COOKIE,
      $_FILES,
      $_SERVER,
      file_get_contents('php://input')
    );

    // log
    $this->app->log = new Log($this->app->config, $this->app->request);

    // initialize the response object
    $this->app->response = new Response();

    // load current uri
    $this->app->request->uri = $this->loadUri();

    // flash messages
    $this->app->flash = new Flash($this->app);
    $this->app->flash->saveFlash($this->app->flash->get());

    // load routes
    $this->app->routes = $this->loadRoutes();

    // get active route (404 as default)
    $this->app->route = $this->getActiveRoute();

    // call active route and get response
    $response = $this->callActiveRoute();
    if(!is_null($response) && !is_bool($response)) {
      $this->app->response = $response;
    }

    // log parsing time
    $execTime = round((microtime(true) - START_TIME) * 1000, 3);
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Message'=>'Request processing completed in '.$execTime.'ms']);

    // return headers
    $headers = $this->app->response->getHeaders();
    foreach($headers as $header) {
      header($header, false);
    }

    echo $this->app->response->getContent();
    exit();
  }

  private function loadBaseConfig() {

    // already set
    if(isset($this->app->config) and !empty($this->app->config)) {
      return $this->app->config;
    }

    // reset config array
    $config = [];

    // load base config
    $files = File::getFileList(CONFIG_BASE);
    foreach($files as $file) {
      include_once CONFIG_BASE.$file;
    }

    $this->app->config = json_decode(json_encode($config));
    return;
  }

  private function loadEnvConfig() {

    // base config should be set already
    if(empty($this->app->config)) {
      $this->loadBaseConfig();
    }

    // reset config array
    $config = [];

    // load environment config, overwrites the base config
    if(File::dirExists(CONFIG_ENV)) {
      $files = File::getFileList(CONFIG_ENV);
      foreach($files as $file) {
        include_once CONFIG_ENV.$file;
      }
    }

    // no env configs
    if(empty($config)) {
      return;
    }

    // convert $this->app->config to a multidimensional array
    $base = json_decode(json_encode($this->app->config), true);

    // merge base and env arrays, then convert from arr to json
    $merged = array_replace_recursive((array) $base, $config);
    $this->app->config = json_decode(json_encode($merged));
    return;
  }

  private function loadUri() {
    $parsed = parse_url($this->app->request->fullUrl);
    $this->app->response->setBaseUrl("{$parsed['scheme']}://{$parsed['host']}:{$parsed['port']}/");
    return '/'.trim($parsed['path'], '/');
  }

  private function loadRoutes() {
    $routes = [];
    if(empty($this->app->routes)) {
      $files = File::getFileList(ROUTES);
      foreach($files as $file) {
        include_once ROUTES.$file;
      }
    }

    // add empty params array if missing
    foreach($routes as $name => $route) {
      if(!isset($route['isAjax'])) {
        $routes[$name]['isAjax'] = false;
      }
      if(!isset($route['isAudit'])) {
        $routes[$name]['isAudit'] = false;
      }
      if(!isset($route['isCron'])) {
        $routes[$name]['isCron'] = false;
      }
      if(!isset($route['params'])) {
        $routes[$name]['params'] = [];
      }
    }

    // load optional routes
    $parsed_routes = [];
    foreach($routes as $name => $route) {
      if(strpos($route['url'], '{') !== false) {

        $route_parts = explode('{', rtrim($route['url'], '}'));

        $url_routes = [];
        foreach($route_parts as $k => $route_part) {
          if($k == 0) {
            $url_routes[$name] = $route_part;
          } else {
            $n = str_replace([':','/'], '', $route_part);
            $url_routes[$name.'_'.$n] = $route_parts[0].$route_part;
          }
        }
        foreach($url_routes as $ur_name => $ur_val) {

          $params = [];
          foreach($route['params'] as $param_key => $param_val) {
            if(strpos($ur_val, $param_key) !== false) {
              $params[$param_key] = $param_val;
            }
          }

          $parsed_routes[$ur_name] = [
            'url' => $ur_val,
            'controller' => $route['controller'],
            'allowedMethods' => $route['allowedMethods'],
            'isProtected' => $route['isProtected'],
            'isAjax' => $route['isAjax'],
            'isAudit' => $route['isAudit'],
            'params' => $params
          ];
        }

      } else {
        $parsed_routes[$name] = $route;
      }
    }

    return json_decode(json_encode($parsed_routes));
  }

  private function getActiveRoute() {

    // has route been found
    $found = false;

    // set default route as 404
    $route = [
      'name' => 'e404',
      'route' => $this->app->routes->e404,
      'params' => [],
      'values' => []
    ];

    // get direct active route
    foreach($this->app->routes as $route_name => $route_details) {
      if(
        $this->app->request->uri === $route_details->url &&
        in_array($this->app->request->getMethod(), (array) $route_details->allowedMethods)
      ) {
        $route = [
          'name' => $route_name,
          'route' => $this->app->routes->{$route_name},
          'params' => [],
          'values' => []
        ];
        $found = true;
        $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>'ROUTING','File'=>__FILE__.':'.__LINE__,'RouteName'=>$route_name,'Controller'=>$route['route']->controller,'Message'=>'Active route found']);
        break;
      }
    }

    // get route with variables. matches urls like /user/1 to /user/{userId}
    // the idea is to match the parameters
    if(!$found):

      // 1. get all routes with params
      $param_routes = [];
      foreach($this->app->routes as $route_name => $route_details) {
        if(strpos($route_details->url, ':')) {
          $param_routes[$route_name] = $route_details;
        }
      }

      // 2. count number of params
      $uri_arr = explode('/', $this->app->request->uri);
      $uri_count = count($uri_arr);

      // 3. loop through the routes with params checking param count
      foreach($param_routes as $pr_name => $pr_route) {

        $pr_route_arr = explode('/', $pr_route->url);
        $pr_route_count = count($pr_route_arr);

        // if counts match, we are a step closer to get the active route
        if($uri_count == $pr_route_count):

          // 4. get differences between uri and url (the params)
          // example: /hello/:id/world and /hello/1/world will return ':id = 1' as the diff
          $diff = array_combine(
            array_diff_assoc($pr_route_arr, $uri_arr),
            array_diff_assoc($uri_arr, $pr_route_arr)
          );
          $diff_count = count($diff);

          // 5. check if number of route params equals the diff_count
          if($diff_count == count((array) $pr_route->params)) {

            // 6. check param regex
            $params_match = [];
            foreach($diff as $diffParam => $diffValue) {
              $diffParam = str_replace(':', '', $diffParam);
              if(preg_match('/^'.$pr_route->params->{$diffParam}.'$/', $diffValue)) {
                $params_match[$diffParam] = $diffValue;
              }
            }

            // if all params matched
            if(count($params_match) == $diff_count) {

              // 7. check method
              if(in_array($this->app->request->getMethod(), (array) $pr_route->allowedMethods)) {
                $route['name'] = $pr_name;
                $route['route'] = $pr_route;
                $route['params'] = $pr_route->params;
                $route['values'] = $params_match;

                $found = true;
                $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>'ROUTING','File'=>__FILE__.':'.__LINE__,'RouteName'=>$route['name'],'Controller'=>$route['route']->controller,'Message'=>'Active method found']);
              }

            }
          }

        endif;

      }
    endif;

    if(!$found) {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>__CLASS__,'Action'=>'ROUTING','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Route not found for URI {$this->app->request->uri}. Using 404 as fallback."]);
    }

    return json_decode(json_encode($route));
  }

  private function callActiveRoute() {

    // simply params
    $route_controller = explode('@', $this->app->route->route->controller);
    $controller = $route_controller[0];
    $method = $route_controller[1];

    // load controller
    $controllerFile = CONTROLLERS.$controller.'.php';
    if(File::exists($controllerFile)) {

      $controller = $this->controllerNamespace.$controller;
      $controllerObj = new $controller($this->app);

      // check controller is subclass of basecontroller
      if(is_subclass_of($controllerObj, '\\SMVC\\Core\\Controller')) {
        if(method_exists($controllerObj, $method) && is_callable([$controllerObj, $method])) {
          return $controllerObj->{$method}();
        } else {
          $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>__CLASS__,'Action'=>'ROUTING','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Method '{$method}' not found or not callable."]);
        }

      } else {
        $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>__CLASS__,'Action'=>'ROUTING','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Controller '{$controller}' found but is not subclass of SMVC\Core\BaseController"]);
      }

    } else {
      $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>__CLASS__,'Action'=>'ROUTING','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Controller file '{$controllerFile}' not found."]);
    }

    // if we get to this point, redirect to 500
    // please make sure the ErrorController@500 controller and method exist or this will result in an infinite call
    $this->app->log->error(['ResponseCode'=>404,'TransactionType'=>__CLASS__,'Action'=>'ROUTING','File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Redirecting to 500."]);

    $this->app->response->setStatusCode(301);
    $this->app->response->redirect('500');
  }
}
