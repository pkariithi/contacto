<?php

/**
 * Measure script execution time
 */
ini_set('precision', 16);
define('START_TIME', microtime(true));

/**
 * env - dev | uat | prod
 * determines which configs to load from the config directory
 * @var string
 */
define('ENV', 'dev');

/**
 * Session and cookie prefixes
 * NOTE: Changing these will invalidate all sessions and cookies
 */
define('SESSION_PREFIX', 'H2NT8_');
define('COOKIE_PREFIX', 'KQX9H_');

/**
 * Session length
 * Amount of time in seconds server should keep session data
 */
define('MAX_SESSION_LIFETIME', 86400);

/**
 * define path variables
 */
define('DS', DIRECTORY_SEPARATOR);
define('EOL', PHP_EOL);

/**
 * define directories
 */
define('ROOT', dirname(__FILE__, 3).DS);

define('PUBLIC_FOLDER', ROOT.'public'.DS);
define('CONFIG_BASE', ROOT.'config'.DS.'base'.DS);
define('CONFIG_ENV', ROOT.'config'.DS.'env'.DS.ENV.DS);
define('ROUTES', ROOT.'routes'.DS);
define('UPLOAD', ROOT.'uploads'.DS);
define('LOG', ROOT.'var'.DS.'logs'.DS);

define('CONTROLLERS', ROOT.'src'.DS.'Controller'.DS);
define('VIEWS', ROOT.'src'.DS.'Views'.DS);

/**
 * Timezone
 */
ini_set('date.timezone', 'Africa/Nairobi');

/**
 * Errors
 */
if(ENV == 'prod') {
  error_reporting(E_ALL);
  ini_set('display_startup_errors', 0);
  ini_set('display_errors', 0);
} else {
  error_reporting(E_ALL);
  ini_set('display_startup_errors', 1);
  ini_set('display_errors', 1);
}

/**
 * Log all errors irrespetive of environment
 */
ini_set('log_errors', 1);
ini_set('error_log', LOG.'php_errors.log');

/**
 * dump function
 */
function dd($vars, $die = true) {
  echo '<pre>';
  print_r($vars);
  echo '</pre>';

  if($die) {
    die();
  }
}

