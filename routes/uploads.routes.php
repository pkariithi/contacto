<?php

$routes['uploads'] = [
  'url' => '/uploads{/:page}',
  'controller' => 'UploadController@listing',
  'allowedMethods' => ['GET','POST'],
  'isProtected' => true,
  'params' => [
    'page' => '[0-9]+'
  ]
];

$routes['upload_new'] = [
  'url' => '/uploads/new',
  'controller' => 'UploadController@new',
  'allowedMethods' => ['GET'],
  'isProtected' => true
];

$routes['upload_process_new'] = [
  'url' => '/uploads/new',
  'controller' => 'UploadController@processNew',
  'allowedMethods' => ['POST'],
  'isProtected' => true
];

$routes['uploads_export'] = [
  'url' => '/uploads/export',
  'controller' => 'UploadController@export',
  'allowedMethods' => ['GET'],
  'isProtected' => true
];

$routes['upload_view'] = [
  'url' => '/uploads/:upload_id/view',
  'controller' => 'UploadController@view',
  'allowedMethods' => ['GET'],
  'isProtected' => true,
  'params' => [
    'upload_id' => '[0-9]+'
  ]
];

$routes['upload_download'] = [
  'url' => '/uploads/:upload_id/download',
  'controller' => 'UploadController@download',
  'allowedMethods' => ['GET'],
  'isProtected' => true,
  'params' => [
    'upload_id' => '[0-9]+'
  ]
];
