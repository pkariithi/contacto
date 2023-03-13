<?php

$routes['messaging'] = [
  'url' => '/messaging{/:page}',
  'controller' => 'MessagingController@listing',
  'allowedMethods' => ['GET','POST'],
  'isProtected' => true,
  'params' => [
    'page' => '[0-9]+'
  ]
];

$routes['messaging_export'] = [
  'url' => '/messaging/export',
  'controller' => 'MessagingController@export',
  'allowedMethods' => ['GET'],
  'isProtected' => true
];

$routes['messaging_view'] = [
  'url' => '/messaging/:messaging_id/view',
  'controller' => 'MessagingController@view',
  'allowedMethods' => ['GET'],
  'isProtected' => true,
  'params' => [
    'messaging_id' => '[0-9]+'
  ]
];
