<?php

$routes['groups'] = [
  'url' => '/groups{/:page}',
  'controller' => 'GroupController@listing',
  'allowedMethods' => ['GET','POST'],
  'isProtected' => true,
  'params' => [
    'page' => '[0-9]+'
  ]
];

$routes['group_new'] = [
  'url' => '/groups/new',
  'controller' => 'GroupController@new',
  'allowedMethods' => ['GET'],
  'isProtected' => true
];

$routes['group_process_new'] = [
  'url' => '/groups/new',
  'controller' => 'GroupController@processNew',
  'allowedMethods' => ['POST'],
  'isProtected' => true
];

$routes['groups_export'] = [
  'url' => '/groups/export',
  'controller' => 'GroupController@export',
  'allowedMethods' => ['GET'],
  'isProtected' => true
];

$routes['group_upload'] = [
  'url' => '/groups/:group_id/upload',
  'controller' => 'GroupController@upload',
  'allowedMethods' => ['GET'],
  'isProtected' => true,
  'params' => [
    'group_id' => '[0-9]+'
  ]
];

$routes['group_process_upload'] = [
  'url' => '/groups/:group_id/upload',
  'controller' => 'GroupController@processUpload',
  'allowedMethods' => ['POST'],
  'isProtected' => true,
  'params' => [
    'group_id' => '[0-9]+'
  ]
];

$routes['group_view'] = [
  'url' => '/groups/:group_id/view',
  'controller' => 'GroupController@view',
  'allowedMethods' => ['GET'],
  'isProtected' => true,
  'params' => [
    'group_id' => '[0-9]+'
  ]
];

$routes['group_edit'] = [
  'url' => '/groups/:group_id/edit',
  'controller' => 'GroupController@edit',
  'allowedMethods' => ['GET'],
  'isProtected' => true,
  'params' => [
    'group_id' => '[0-9]+'
  ]
];

$routes['group_process_edit'] = [
  'url' => '/groups/:group_id/edit',
  'controller' => 'GroupController@processEdit',
  'allowedMethods' => ['POST'],
  'isProtected' => true,
  'params' => [
    'group_id' => '[0-9]+'
  ]
];

$routes['group_delete'] = [
  'url' => '/groups/:group_id/delete',
  'controller' => 'GroupController@delete',
  'allowedMethods' => ['GET'],
  'isProtected' => true,
  'params' => [
    'group_id' => '[0-9]+'
  ]
];

$routes['group_process_delete'] = [
  'url' => '/groups/:group_id/delete',
  'controller' => 'GroupController@processDelete',
  'allowedMethods' => ['POST'],
  'isProtected' => true,
  'params' => [
    'group_id' => '[0-9]+'
  ]
];

$routes['group_enable'] = [
  'url' => '/groups/:group_id/enable',
  'controller' => 'GroupController@enable',
  'allowedMethods' => ['GET'],
  'isProtected' => true,
  'params' => [
    'group_id' => '[0-9]+'
  ]
];

$routes['group_process_enable'] = [
  'url' => '/groups/:group_id/enable',
  'controller' => 'GroupController@processEnable',
  'allowedMethods' => ['POST'],
  'isProtected' => true,
  'params' => [
    'group_id' => '[0-9]+'
  ]
];

$routes['group_disable'] = [
  'url' => '/groups/:group_id/disable',
  'controller' => 'GroupController@disable',
  'allowedMethods' => ['GET'],
  'isProtected' => true,
  'params' => [
    'group_id' => '[0-9]+'
  ]
];

$routes['group_process_disable'] = [
  'url' => '/groups/:group_id/disable',
  'controller' => 'GroupController@processDisable',
  'allowedMethods' => ['POST'],
  'isProtected' => true,
  'params' => [
    'group_id' => '[0-9]+'
  ]
];

$routes['group_contacts'] = [
  'url' => '/groups/:group_id/contacts',
  'controller' => 'GroupController@contacts',
  'allowedMethods' => ['GET'],
  'isProtected' => true,
  'params' => [
    'group_id' => '[0-9]+'
  ]
];

$routes['group_process_contacts'] = [
  'url' => '/groups/:group_id/contacts',
  'controller' => 'GroupController@processContacts',
  'allowedMethods' => ['POST'],
  'isProtected' => true,
  'params' => [
    'group_id' => '[0-9]+'
  ]
];

$routes['group_send_sms'] = [
  'url' => '/groups/:group_id/send-sms',
  'controller' => 'GroupController@sendSms',
  'allowedMethods' => ['GET'],
  'isProtected' => true,
  'params' => [
    'group_id' => '[0-9]+'
  ]
];

$routes['group_process_send_sms'] = [
  'url' => '/groups/:group_id/send-sms',
  'controller' => 'GroupController@processSendSms',
  'allowedMethods' => ['POST'],
  'isProtected' => true,
  'params' => [
    'group_id' => '[0-9]+'
  ]
];

$routes['group_send_email'] = [
  'url' => '/groups/:group_id/send-email',
  'controller' => 'GroupController@sendEmail',
  'allowedMethods' => ['GET'],
  'isProtected' => true,
  'params' => [
    'group_id' => '[0-9]+'
  ]
];

$routes['group_process_send_email'] = [
  'url' => '/groups/:group_id/send-email',
  'controller' => 'GroupController@processSendEmail',
  'allowedMethods' => ['POST'],
  'isProtected' => true,
  'params' => [
    'group_id' => '[0-9]+'
  ]
];

$routes['groups_export_contacts'] = [
  'url' => '/groups/:group_id/export-contacts',
  'controller' => 'GroupController@exportContacts',
  'allowedMethods' => ['GET'],
  'isProtected' => true,
  'params' => [
    'group_id' => '[0-9]+'
  ]
];
