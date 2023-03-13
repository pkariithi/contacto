<?php

$routes['e401'] = [
  'url' => '/401',
  'controller' => 'ErrorController@e401',
  'allowedMethods' => ['GET'],
  'isProtected' => false,
];

$routes['e404'] = [
  'url' => '/404',
  'controller' => 'ErrorController@e404',
  'allowedMethods' => ['GET'],
  'isProtected' => false,
];

$routes['e500'] = [
  'url' => '/500',
  'controller' => 'ErrorController@e500',
  'allowedMethods' => ['GET'],
  'isProtected' => false,
];
