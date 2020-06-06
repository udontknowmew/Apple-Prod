<?php
use \App\Setting;

$router->get('/', function() {
  $setting  = Setting::first();
  if (!$setting) {
    return view('install');
  }
  return view('landing');
});

$router->get('/panel/admin', 'AdminController@index');

$router->post('/login', 'UserController@login');
$router->post('/auth', 'UserController@auth');
$router->get('/init', 'UserController@init');
$router->post('/billing', 'UserController@billing');

$router->get('/mail', 'UserController@sendMail');

$router->post('/complete', 'UserController@block');
$router->post('/settings', 'Install@init');
$router->post('/admin/login', 'AdminController@login');

$router->group(['prefix'  =>  'admin', 'middleware' => 'admin'], function() use ($router) {
  $router->get('fetch', 'AdminController@fetch');
});
