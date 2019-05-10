<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('admin.home');
    $router->resource('car',CarInfoController::class);
    /*$router->get('/car', 'CarInfoController@index')->name('admin.car');
    $router->get('/car/create', 'CarInfoController@create')->name('admin.car.create');
    $router->get('/examine', 'CarExamineController@index')->name('admin.examine');*/

});
