<?php

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api'],
    function () use ($router) {

        $router->post('auth/login', ['uses' => 'Auth\AuthController@userAuthenticate']);
        $router->post('users', ['uses' => 'UserController@create']);
        $router->post('users/verify', ['uses' => 'UserController@verifyUser']);

        $router->group(['middleware' => 'jwt.auth'],
            function () use ($router) {
                $router->get('users', ['uses' => 'UserController@showAllUsers']);

                /** Users */
                $router->post('users/verification-code', ['uses' => 'UserController@getVerificationCode']);
                $router->post('users/{id}', ['uses' => 'UserController@update']);

                /** Home */
                $router->post('cities', ['uses' => 'HomeController@districtList']);
                $router->post('areas/{id}', ['uses' => 'HomeController@areaList']);
                $router->post('companies/all', ['uses' => 'HomeController@CompanyList']);
                $router->get('data-sync', ['uses' => 'HomeController@dataSync']);

                /** Medicine */
                $router->get('medicines/search', ['uses' => 'MedicineController@search']);
                $router->post('medicines/company', ['uses' => 'MedicineController@searchByCompany']);
                $router->get('companies', ['uses' => 'CompanyController@index']); // only name of all companies

                /** Carts */
                $router->post('carts/add-to-cart', ['uses' => 'CartController@addToCart']);
                $router->get('carts/{token}', ['uses' => 'CartController@view']);
                $router->get('carts/{token}/check', ['uses' => 'CartController@tokenCheck']);
                $router->post('carts/delete-item', ['uses' => 'CartController@deleteItem']);
                $router->post('carts/quantity-update', ['uses' => 'CartController@quantityUpdate']);

                /** Order */
                $router->post('orders', ['uses' => 'OrderController@create']);
                $router->post('orders/manual', ['uses' => 'OrderController@manualOrder']);
                $router->get('orders', ['uses' => 'OrderController@index']);
                $router->get('orders/{token}', ['uses' => 'OrderController@view']);
                $router->post('orders/update', ['uses' => 'OrderController@update']);
                $router->post('orders/update-status', ['uses' => 'OrderController@statusUpdate']);
                $router->post('orders/delete-item', ['uses' => 'OrderController@deleteItem']);
                $router->get('orders/check-is-last-item/{item_id}', ['uses' => 'OrderController@checkIsLastItem']); // unused

                /** MR Connection */
                $router->post('mr-connection', ['uses' => 'UserController@mrConnection']);

                /** Reports */
                $router->get('reports/purchase-manual', ['uses' => 'OrderController@manualOrderList']);

            }
        );

    }
);
/** Script for database migration */
$router->get('medicine-scripe', ['uses' => 'TestController@medicineScript']);
$router->get('medicine-type', ['uses' => 'TestController@medicineTypeScript']);
$router->get('test', ['uses' => 'TestController@test']);

$router->post('orders/sync/data', ['uses' => 'HomeController@awsData']);

$router->post('data_sync', ['uses' => 'HomeController@dataSyncToDB']);
$router->post('sync-data-to-server', ['uses' => 'HomeController@syncDataToServer']);