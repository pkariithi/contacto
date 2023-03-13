<?php

$routes['users'] = [
  'url' => '/users{/:page}',
  'controller' => 'UserController@listing',
  'allowedMethods' => ['GET','POST'],
  'isProtected' => true,
  'params' => [
    'page' => '[0-9]+'
  ]
];

$routes['user_new'] = [
  'url' => '/users/new',
  'controller' => 'UserController@new',
  'allowedMethods' => ['GET'],
  'isProtected' => true
];

$routes['user_process_new'] = [
  'url' => '/users/new',
  'controller' => 'UserController@processNew',
  'allowedMethods' => ['POST'],
  'isProtected' => true
];

$routes['user_view'] = [
  'url' => '/users/:user_id/view',
  'controller' => 'UserController@view',
  'allowedMethods' => ['GET'],
  'isProtected' => true,
  'params' => [
    'user_id' => '[0-9]+'
  ]
];

$routes['user_export'] = [
  'url' => '/users/export',
  'controller' => 'UserController@export',
  'allowedMethods' => ['GET'],
  'isProtected' => true
];

$routes['user_edit'] = [
  'url' => '/users/:user_id/edit',
  'controller' => 'UserController@edit',
  'allowedMethods' => ['GET'],
  'isProtected' => true,
  'params' => [
    'user_id' => '[0-9]+'
  ]
];

$routes['user_process_edit'] = [
  'url' => '/users/:user_id/edit',
  'controller' => 'UserController@processEdit',
  'allowedMethods' => ['POST'],
  'isProtected' => true,
  'params' => [
    'user_id' => '[0-9]+'
  ]
];

$routes['user_delete'] = [
  'url' => '/users/:user_id/delete',
  'controller' => 'UserController@delete',
  'allowedMethods' => ['GET'],
  'isProtected' => true,
  'params' => [
    'user_id' => '[0-9]+'
  ]
];

$routes['user_process_delete'] = [
  'url' => '/users/:user_id/delete',
  'controller' => 'UserController@processDelete',
  'allowedMethods' => ['POST'],
  'isProtected' => true,
  'params' => [
    'user_id' => '[0-9]+'
  ]
];

$routes['user_enable'] = [
  'url' => '/users/:user_id/enable',
  'controller' => 'UserController@enable',
  'allowedMethods' => ['GET'],
  'isProtected' => true,
  'params' => [
    'user_id' => '[0-9]+'
  ]
];

$routes['user_process_enable'] = [
  'url' => '/users/:user_id/enable',
  'controller' => 'UserController@processEnable',
  'allowedMethods' => ['POST'],
  'isProtected' => true,
  'params' => [
    'user_id' => '[0-9]+'
  ]
];

$routes['user_disable'] = [
  'url' => '/users/:user_id/disable',
  'controller' => 'UserController@disable',
  'allowedMethods' => ['GET'],
  'isProtected' => true,
  'params' => [
    'user_id' => '[0-9]+'
  ]
];

$routes['user_process_disable'] = [
  'url' => '/users/:user_id/disable',
  'controller' => 'UserController@processDisable',
  'allowedMethods' => ['POST'],
  'isProtected' => true,
  'params' => [
    'user_id' => '[0-9]+'
  ]
];

$routes['user_close'] = [
  'url' => '/users/:user_id/close',
  'controller' => 'UserController@close',
  'allowedMethods' => ['GET'],
  'isProtected' => true,
  'params' => [
    'user_id' => '[0-9]+'
  ]
];

$routes['user_process_close'] = [
  'url' => '/users/:user_id/close',
  'controller' => 'UserController@processClose',
  'allowedMethods' => ['POST'],
  'isProtected' => true,
  'params' => [
    'user_id' => '[0-9]+'
  ]
];

$routes['user_roles'] = [
  'url' => '/users/:user_id/roles',
  'controller' => 'UserController@roles',
  'allowedMethods' => ['GET'],
  'isProtected' => true,
  'params' => [
    'user_id' => '[0-9]+'
  ]
];

$routes['user_process_roles'] = [
  'url' => '/users/:user_id/roles',
  'controller' => 'UserController@processRoles',
  'allowedMethods' => ['POST'],
  'isProtected' => true,
  'params' => [
    'user_id' => '[0-9]+'
  ]
];
