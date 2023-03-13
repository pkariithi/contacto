<?php

$routes['profile'] = [
  'url' => '/profile',
  'controller' => 'ProfileController@profile',
  'allowedMethods' => ['GET'],
  'isProtected' => true,
];
