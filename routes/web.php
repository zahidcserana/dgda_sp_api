<?php

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api'],
    function () use ($router) {
        $router->group(['middleware' => 'jwt.auth'],
            function () use ($router) {
                $router->get('users', ['uses' => 'UserController@showAllUsers']);

                /** Medicine */
                $router->get('medicines/search', ['uses' => 'MedicineController@search']);
                $router->post('medicines/company', ['uses' => 'MedicineController@searchByCompany']);
                $router->get('companies', ['uses' => 'CompanyController@index']);

                /** Carts */
                $router->post('carts/add-to-cart', ['uses' => 'CartController@addToCart']);
                $router->get('carts/{token}', ['uses' => 'CartController@view']);
                $router->post('carts/delete-item', ['uses' => 'CartController@deleteItem']);
                $router->post('carts/quantity-update', ['uses' => 'CartController@quantityUpdate']);

                /** Order */
                $router->post('orders', ['uses' => 'OrderController@create']);
                $router->get('orders', ['uses' => 'OrderController@index']);
                $router->get('orders/{token}', ['uses' => 'OrderController@view']);


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


