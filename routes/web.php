<?php

$router->post('/', function () use ($router) {
    return $router->app->version();
});

$router->post(
    'auth/login', 
    [
       'uses' => 'Auth\AuthController@userAuthenticate'
    ]
);

$router->group(['prefix' => 'api','middleware' => 'jwt.auth'], 
    function() use ($router) {
    	$router->get('users',  ['uses' => 'UserController@showAllUsers']);
    }
);

// $router->group(['prefix' => 'api'], function () use ($router) {
//   $router->get('users',  ['uses' => 'UserController@showAllUsers']);

//   $router->get('users/{id}', ['uses' => 'UserController@showOneUser']);

//   $router->post('users', ['uses' => 'UserController@create']);

//   $router->delete('users/{id}', ['uses' => 'UserController@delete']);

//   $router->put('users/{id}', ['uses' => 'UserController@update']);
// });