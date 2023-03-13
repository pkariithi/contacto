<?php

$routes['login'] = [
  'url' => '/',
  'controller' => 'AuthController@login',
  'allowedMethods' => ['GET'],
  'isProtected' => false,
];

$routes['processlogin'] = [
  'url' => '/',
  'controller' => 'AuthController@processLogin',
  'allowedMethods' => ['POST'],
  'isProtected' => false,
];

$routes['register'] = [
  'url' => '/register',
  'controller' => 'AuthController@register',
  'allowedMethods' => ['GET'],
  'isProtected' => false,
];

$routes['process_register'] = [
  'url' => '/register',
  'controller' => 'AuthController@processRegister',
  'allowedMethods' => ['POST'],
  'isProtected' => false,
];

$routes['forgot'] = [
  'url' => '/forgot',
  'controller' => 'AuthController@forgot',
  'allowedMethods' => ['GET'],
  'isProtected' => false,
];

$routes['process_forgot'] = [
  'url' => '/forgot',
  'controller' => 'AuthController@processForgot',
  'allowedMethods' => ['POST'],
  'isProtected' => false,
];

$routes['reset'] = [
  'url' => '/reset/:token',
  'controller' => 'AuthController@reset',
  'allowedMethods' => ['GET'],
  'isProtected' => false,
  'params' => [
    'token' => '[0-9A-Za-z]+'
  ]
];

$routes['process_reset'] = [
  'url' => '/reset/:token',
  'controller' => 'AuthController@processReset',
  'allowedMethods' => ['POST'],
  'isProtected' => false,
  'params' => [
    'token' => '[0-9A-Za-z]+'
  ]
];

$routes['logout'] = [
  'url' => '/logout',
  'controller' => 'AuthController@logout',
  'allowedMethods' => ['GET'],
  'isProtected' => false,
];
