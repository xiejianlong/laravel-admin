<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('admin.home');

    //$router->resource('car','CarInfoController');
    //查看车辆信息
    $router->get('/car','CarInfoController@index')->name('car.list');
    //创建车辆信息form
    $router->get('/car/create','CarInfoController@create')->name('car.create');
    //创建车辆信息提交数据
    $router->post('/car','CarInfoController@store')->name('car.create.store');
    //编辑车辆信息form
    $router->get('/car/{id}/edit','CarInfoController@edit')->name('car.edit');
    //编辑车辆信息提交数据
    $router->put('/car/{id}','CarInfoController@update')->name('car.update');
    //删除车辆信息
    $router->delete('/car/{id}','CarInfoController@destroy')->name('car.destroy');


    $router->resource('examine','CarExamineController');
    //申请车辆-form
    $router->get('/examine/create','CarExamineController@create')->name('caexaminer.create');
    $router->get('/apply/do','ApplyController@doApply');
    $router->post('/apply/create','ApplyController@store');

    $router->get('/test','ApplyController@test');

    /*$router->get('/examine/create/{id}','CarExamineController@create');

    $router->post('/examine/create/{id}','CarExamineController@create');

    $router->get('/examine','CarExamineController@index');*/


});
