<?php

/*
accounts:
  superadmin@email.com - SuperAdmin123!
  admin@email.com - Admin123!
  auditor@email.com - Auditor123!
  user@email.com - User123!
*/

/**
 * Bootstrap the application
 * This sets stuff like the environment, autoloader, constants, paths, etc
 */
require_once '../src/Core/Bootstrap.php';

// autoload
require_once '../vendor/autoload.php';

/**
 * start the application
 */
new SMVC\Core\Application();
