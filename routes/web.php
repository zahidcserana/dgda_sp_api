<?php

$router->post('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api'],
    function () use ($router) {
        $router->group(['middleware' => 'jwt.auth'],
            function () use ($router) {
                $router->get('users', ['uses' => 'UserController@showAllUsers']);
                $router->get('medicines', ['uses' => 'MedicineController@search']);
            }
        );

        $router->post(
            'auth/login',
            [
                'uses' => 'Auth\AuthController@userAuthenticate'
            ]
        );
    }
);
    /** Script for database migration */
   $router->get('medicine-scripe',  ['uses' => 'TestController@medicineScript']);
   $router->get('medicine-type',  ['uses' => 'TestController@medicineTypeScript']);


