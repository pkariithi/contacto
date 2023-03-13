<?php

$routes['roles'] = [
  'url' => '/roles{/:page}',
  'controller' => 'RoleController@listing',
  'allowedMethods' => ['GET','POST'],
  'isProtected' => true,
  'params' => [
    'page' => '[0-9]+'
  ]
];

$routes['role_new'] = [
  'url' => '/roles/new',
  'controller' => 'RoleController@new',
  'allowedMethods' => ['GET'],
  'isProtected' => true
];

$routes['role_process_new'] = [
  'url' => '/roles/new',
  'controller' => 'RoleController@processNew',
  'allowedMethods' => ['POST'],
  'isProtected' => true
];

$routes['role_view'] = [
  'url' => '/roles/:role_id/view',
  'controller' => 'RoleController@view',
  'allowedMethods' => ['GET'],
  'isProtected' => true,
  'params' => [
    'role_id' => '[0-9]+'
  ]
];

$routes['role_export'] = [
  'url' => '/roles/export',
  'controller' => 'RoleController@export',
  'allowedMethods' => ['GET'],
  'isProtected' => true
];

$routes['role_edit'] = [
  'url' => '/roles/:role_id/edit',
  'controller' => 'RoleController@edit',
  'allowedMethods' => ['GET'],
  'isProtected' => true,
  'params' => [
    'role_id' => '[0-9]+'
  ]
];

$routes['role_process_edit'] = [
  'url' => '/roles/:role_id/edit',
  'controller' => 'RoleController@processEdit',
  'allowedMethods' => ['POST'],
  'isProtected' => true,
  'params' => [
    'role_id' => '[0-9]+'
  ]
];

$routes['role_delete'] = [
  'url' => '/roles/:role_id/delete',
  'controller' => 'RoleController@delete',
  'allowedMethods' => ['GET'],
  'isProtected' => true,
  'params' => [
    'role_id' => '[0-9]+'
  ]
];

$routes['role_process_delete'] = [
  'url' => '/roles/:role_id/delete',
  'controller' => 'RoleController@processDelete',
  'allowedMethods' => ['POST'],
  'isProtected' => true,
  'params' => [
    'role_id' => '[0-9]+'
  ]
];

$routes['role_enable'] = [
  'url' => '/roles/:role_id/enable',
  'controller' => 'RoleController@enable',
  'allowedMethods' => ['GET'],
  'isProtected' => true,
  'params' => [
    'role_id' => '[0-9]+'
  ]
];

$routes['role_process_enable'] = [
  'url' => '/roles/:role_id/enable',
  'controller' => 'RoleController@processEnable',
  'allowedMethods' => ['POST'],
  'isProtected' => true,
  'params' => [
    'role_id' => '[0-9]+'
  ]
];

$routes['role_disable'] = [
  'url' => '/roles/:role_id/disable',
  'controller' => 'RoleController@disable',
  'allowedMethods' => ['GET'],
  'isProtected' => true,
  'params' => [
    'role_id' => '[0-9]+'
  ]
];

$routes['role_process_disable'] = [
  'url' => '/roles/:role_id/disable',
  'controller' => 'RoleController@processDisable',
  'allowedMethods' => ['POST'],
  'isProtected' => true,
  'params' => [
    'role_id' => '[0-9]+'
  ]
];

$routes['role_permissions'] = [
  'url' => '/roles/:role_id/permissions',
  'controller' => 'RoleController@permissions',
  'allowedMethods' => ['GET'],
  'isProtected' => true,
  'params' => [
    'role_id' => '[0-9]+'
  ]
];

$routes['role_process_permissions'] = [
  'url' => '/roles/:role_id/permissions',
  'controller' => 'RoleController@processPermissions',
  'allowedMethods' => ['POST'],
  'isProtected' => true,
  'params' => [
    'role_id' => '[0-9]+'
  ]
];

$routes['role_users'] = [
  'url' => '/roles/:role_id/users',
  'controller' => 'RoleController@users',
  'allowedMethods' => ['GET'],
  'isProtected' => true,
  'params' => [
    'role_id' => '[0-9]+'
  ]
];

$routes['role_process_users'] = [
  'url' => '/roles/:role_id/users',
  'controller' => 'RoleController@processUsers',
  'allowedMethods' => ['POST'],
  'isProtected' => true,
  'params' => [
    'role_id' => '[0-9]+'
  ]
];
