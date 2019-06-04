<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('admin.home');

    $router->resource('car','CarInfoController');
    $router->resource('examine','CarExamineController');

    $router->get('/apply/do','ApplyController@doApply');
    $router->post('/apply/create','ApplyController@store');

    $router->get('/test','ApplyController@test');

    /*$router->get('/examine/create/{id}','CarExamineController@create');

    $router->post('/examine/create/{id}','CarExamineController@create');

    $router->get('/examine','CarExamineController@index');*/


});
