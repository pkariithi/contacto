<?php

namespace SMVC\Helpers;

use SMVC\Helpers\Date;

class Log {

  private $request;
  private $response;
  private $config;
  private $vars = [];

  public function __construct($config, Request $request, Response $response) {
    $this->config = $config;
    $this->request = $request;
    $this->response = $response;
  }

  public function info($vars, $line = null) {
    $this->vars = $this->mergeVars($vars);
    $this->write('info');
  }

  public function debug($vars) {
    $this->vars = $this->mergeVars($vars);
    $this->write('debug');
  }

  public function error($vars, $line = null) {
    $this->vars = $this->mergeVars($vars);
    $this->write('error');
  }

  public function sql($vars) {
    $this->vars = $this->mergeVars($vars);
    $this->write('sql');
  }

  public function auth($vars) {
    $this->vars = $this->mergeVars($vars);
    $this->write('auth');
  }

  private function mergeVars($vars = []) {

    // some HTTP variables
    $useragent = $this->request->getUserAgent();
    $method = $this->request->getMethod();

    // defaults - order in which they will be written in the log file
    $defaults = [
      'TransationID' => microtime(true),
      'LogID' => null,
      'RequestURL' => str_replace($this->response->getBaseUrl(),'', $this->request->fullUrl),
      'HTTPMethod' => $method,
      'TransactionType' => null,
      'Action' => null,
      'Controller' => null,
      'File' => null,
      'ResponseCode' => 200,
      'IpAddress' => $this->request->getIpAddress(),
      //'SourceSystem' => 'M-Pesa Statements Portal',
      //'TargetSystem' => 'M-Pesa Statements Portal',
      // 'HTTPUserAgent' => $useragent,
      //'BaseURL' => $this->config->app->base_href,
      //'RequestURL' => str_replace($this->config->app->base_href,'', $this->request->fullUrl),
      'Extrafield' => null,
      'OldFile' => null,
      //'ResponseTime' => null,
      'ErrorDescription' => null,
      'Message' => null
    ];

    // add logid
    if(isset($_SESSION) && isset($_SESSION[SESSION_PREFIX.'log_session_id'])) {
      $defaults['LogID'] = $_SESSION[SESSION_PREFIX.'log_session_id'];
    }

    // merge vars to defaults
    $merged = [];
    foreach($defaults as $k => $v) {
      $merged[$k] = $v;
      if(isset($vars[$k])) {
        $merged[$k] = $vars[$k];
      }
    }

    // format
    foreach($merged as $mk => &$mv) {
      switch($mk) {
        case 'File':
          $mv = str_replace(ROOT, '', $mv);
          break;
        case 'TransactionType':
        case 'Message':
        case 'ErrorDescription':
          $mv = str_replace(['SMVC\\Controller\\', 'SMVC\\Core\\'], ['',''], $mv);
          break;
      }

      // remove empty
      if(empty($mv) && !in_array($mv, ['ResponseCode','TransationID','TransactionType','Action'])) {
        unset($merged[$mk]);
      }
    }

    return $merged;
  }

  private function write($type) {

    // logging is disabled from the log configs
    if(!$this->config->log->enabled) {
      return false;
    }

    // options
    $date = Date::now('Y-m-d H:i:s.u');
    $today = Date::now('Y-m-d');
    $type = str_pad(mb_strtoupper($type), 5, ' ', STR_PAD_RIGHT);

    // log vars
    $vars_string = "| {$type} ";
    foreach($this->vars as $key => $val) {
      $vars_string .= "| {$key}={$val} ";
    }
    $vars_string = trim($vars_string);

    // log message
    $text = "{$date} {$vars_string}\n\n";

    // write to file
    $logFile = LOG.$today.'.log';
    File::writeFileContent($text, $logFile, true, true);
  }

}
