<?php

$routes['permissions'] = [
  'url' => '/permissions{/:page}',
  'controller' => 'PermissionController@listing',
  'allowedMethods' => ['GET','POST'],
  'isProtected' => true,
  'params' => [
    'page' => '[0-9]+'
  ]
];

$routes['permission_view'] = [
  'url' => '/permissions/:permission_id/view',
  'controller' => 'PermissionController@view',
  'allowedMethods' => ['GET'],
  'isProtected' => true,
  'params' => [
    'permission_id' => '[0-9]+'
  ]
];

$routes['permission_export'] = [
  'url' => '/permissions/export',
  'controller' => 'PermissionController@export',
  'allowedMethods' => ['GET'],
  'isProtected' => true
];

$routes['permission_enable'] = [
  'url' => '/permissions/:permission_id/enable',
  'controller' => 'PermissionController@enable',
  'allowedMethods' => ['GET'],
  'isProtected' => true,
  'params' => [
    'permission_id' => '[0-9]+'
  ]
];

$routes['permission_process_enable'] = [
  'url' => '/permissions/:permission_id/enable',
  'controller' => 'PermissionController@processEnable',
  'allowedMethods' => ['POST'],
  'isProtected' => true,
  'params' => [
    'permission_id' => '[0-9]+'
  ]
];

$routes['permission_disable'] = [
  'url' => '/permissions/:permission_id/disable',
  'controller' => 'PermissionController@disable',
  'allowedMethods' => ['GET'],
  'isProtected' => true,
  'params' => [
    'permission_id' => '[0-9]+'
  ]
];

$routes['permission_process_disable'] = [
  'url' => '/permissions/:permission_id/disable',
  'controller' => 'PermissionController@processDisable',
  'allowedMethods' => ['POST'],
  'isProtected' => true,
  'params' => [
    'permission_id' => '[0-9]+'
  ]
];
