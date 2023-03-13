<?php

$routes['sessions'] = [
  'url' => '/sessions{/:page}',
  'controller' => 'SessionController@listing',
  'allowedMethods' => ['GET','POST'],
  'isProtected' => true,
  'params' => [
    'page' => '[0-9]+'
  ]
];

$routes['session_view'] = [
  'url' => '/sessions/:session_id/view',
  'controller' => 'SessionController@view',
  'allowedMethods' => ['GET'],
  'isProtected' => true,
  'params' => [
    'session_id' => '[0-9]+'
  ]
];

$routes['session_export'] = [
  'url' => '/sessions/export',
  'controller' => 'SessionController@export',
  'allowedMethods' => ['GET'],
  'isProtected' => true
];