<?php

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api'],
    function () use ($router) {
        $router->group(['middleware' => 'jwt.auth'],
            function () use ($router) {
                $router->get('users', ['uses' => 'UserController@showAllUsers']);

                /** Users */
                $router->post('users', ['uses' => 'UserController@create']);
                $router->post('users/verify', ['uses' => 'UserController@verifyUser']);
                $router->post('users/verification-code', ['uses' => 'UserController@getVerificationCode']);
                $router->post('users/{id}', ['uses' => 'UserController@update']);

                /** Home */
                $router->post('cities', ['uses' => 'HomeController@districtList']);
                $router->post('companies/all', ['uses' => 'HomeController@CompanyList']);

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
                $router->post('orders/update', ['uses' => 'OrderController@update']);

                /** MR Connection */
                $router->post('mr-connection', ['uses' => 'UserController@mrConnection']);

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
$router->get('medicine-scripe', ['uses' => 'TestController@medicineScript']);
$router->get('medicine-type', ['uses' => 'TestController@medicineTypeScript']);


