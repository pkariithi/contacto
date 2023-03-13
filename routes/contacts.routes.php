<?php

$routes['contacts'] = [
  'url' => '/contacts{/:page}',
  'controller' => 'ContactController@listing',
  'allowedMethods' => ['GET','POST'],
  'isProtected' => true,
  'params' => [
    'page' => '[0-9]+'
  ]
];

$routes['contact_new'] = [
  'url' => '/contacts/new',
  'controller' => 'ContactController@new',
  'allowedMethods' => ['GET'],
  'isProtected' => true
];

$routes['contact_process_new'] = [
  'url' => '/contacts/new',
  'controller' => 'ContactController@processNew',
  'allowedMethods' => ['POST'],
  'isProtected' => true
];

$routes['contacts_export'] = [
  'url' => '/contacts/export',
  'controller' => 'ContactController@export',
  'allowedMethods' => ['GET'],
  'isProtected' => true
];

$routes['contact_upload'] = [
  'url' => '/contacts/upload',
  'controller' => 'ContactController@upload',
  'allowedMethods' => ['GET'],
  'isProtected' => true
];

$routes['contact_process_upload'] = [
  'url' => '/contacts/upload',
  'controller' => 'ContactController@processUpload',
  'allowedMethods' => ['POST'],
  'isProtected' => true
];

$routes['contact_view'] = [
  'url' => '/contacts/:contact_id/view',
  'controller' => 'ContactController@view',
  'allowedMethods' => ['GET'],
  'isProtected' => true,
  'params' => [
    'contact_id' => '[0-9]+'
  ]
];

$routes['contact_edit'] = [
  'url' => '/contacts/:contact_id/edit',
  'controller' => 'ContactController@edit',
  'allowedMethods' => ['GET'],
  'isProtected' => true,
  'params' => [
    'contact_id' => '[0-9]+'
  ]
];

$routes['contact_process_edit'] = [
  'url' => '/contacts/:contact_id/edit',
  'controller' => 'ContactController@processEdit',
  'allowedMethods' => ['POST'],
  'isProtected' => true,
  'params' => [
    'contact_id' => '[0-9]+'
  ]
];

$routes['contact_delete'] = [
  'url' => '/contacts/:contact_id/delete',
  'controller' => 'ContactController@delete',
  'allowedMethods' => ['GET'],
  'isProtected' => true,
  'params' => [
    'contact_id' => '[0-9]+'
  ]
];

$routes['contact_process_delete'] = [
  'url' => '/contacts/:contact_id/delete',
  'controller' => 'ContactController@processDelete',
  'allowedMethods' => ['POST'],
  'isProtected' => true,
  'params' => [
    'contact_id' => '[0-9]+'
  ]
];

$routes['contact_enable'] = [
  'url' => '/contacts/:contact_id/enable',
  'controller' => 'ContactController@enable',
  'allowedMethods' => ['GET'],
  'isProtected' => true,
  'params' => [
    'contact_id' => '[0-9]+'
  ]
];

$routes['contact_process_enable'] = [
  'url' => '/contacts/:contact_id/enable',
  'controller' => 'ContactController@processEnable',
  'allowedMethods' => ['POST'],
  'isProtected' => true,
  'params' => [
    'contact_id' => '[0-9]+'
  ]
];

$routes['contact_disable'] = [
  'url' => '/contacts/:contact_id/disable',
  'controller' => 'ContactController@disable',
  'allowedMethods' => ['GET'],
  'isProtected' => true,
  'params' => [
    'contact_id' => '[0-9]+'
  ]
];

$routes['contact_process_disable'] = [
  'url' => '/contacts/:contact_id/disable',
  'controller' => 'ContactController@processDisable',
  'allowedMethods' => ['POST'],
  'isProtected' => true,
  'params' => [
    'contact_id' => '[0-9]+'
  ]
];

$routes['contact_close'] = [
  'url' => '/contacts/:contact_id/close',
  'controller' => 'ContactController@close',
  'allowedMethods' => ['GET'],
  'isProtected' => true,
  'params' => [
    'contact_id' => '[0-9]+'
  ]
];

$routes['contact_process_close'] = [
  'url' => '/contacts/:contact_id/close',
  'controller' => 'ContactController@processClose',
  'allowedMethods' => ['POST'],
  'isProtected' => true,
  'params' => [
    'contact_id' => '[0-9]+'
  ]
];

$routes['contact_groups'] = [
  'url' => '/contacts/:contact_id/groups',
  'controller' => 'ContactController@groups',
  'allowedMethods' => ['GET'],
  'isProtected' => true,
  'params' => [
    'contact_id' => '[0-9]+'
  ]
];

$routes['contact_process_groups'] = [
  'url' => '/contacts/:contact_id/groups',
  'controller' => 'ContactController@processGroups',
  'allowedMethods' => ['POST'],
  'isProtected' => true,
  'params' => [
    'contact_id' => '[0-9]+'
  ]
];

$routes['contact_send_sms'] = [
  'url' => '/contacts/:contact_id/send-sms',
  'controller' => 'ContactController@sendSms',
  'allowedMethods' => ['GET'],
  'isProtected' => true,
  'params' => [
    'contact_id' => '[0-9]+'
  ]
];

$routes['contact_process_send_sms'] = [
  'url' => '/contacts/:contact_id/send-sms',
  'controller' => 'ContactController@processSendSms',
  'allowedMethods' => ['POST'],
  'isProtected' => true,
  'params' => [
    'contact_id' => '[0-9]+'
  ]
];

$routes['contact_send_email'] = [
  'url' => '/contacts/:contact_id/send-email',
  'controller' => 'ContactController@sendEmail',
  'allowedMethods' => ['GET'],
  'isProtected' => true,
  'params' => [
    'contact_id' => '[0-9]+'
  ]
];

$routes['contact_process_send_email'] = [
  'url' => '/contacts/:contact_id/send-email',
  'controller' => 'ContactController@processSendEmail',
  'allowedMethods' => ['POST'],
  'isProtected' => true,
  'params' => [
    'contact_id' => '[0-9]+'
  ]
];