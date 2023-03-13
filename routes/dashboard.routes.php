<?php

$routes['dashboard'] = [
  'url' => '/dashboard',
  'controller' => 'DashboardController@dashboard',
  'allowedMethods' => ['GET','POST'],
  'isProtected' => true,
];
