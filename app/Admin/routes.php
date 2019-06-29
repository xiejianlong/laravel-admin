<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'CarInfoController@index')->name('car.list');

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

    //申请车辆-form
    $router->get('/examine/create','CarExamineController@create')->name('examiner.create');
    //提交申请
    $router->post('/examine','CarExamineController@store')->name('examiner.create.store');
    //申请列表
    $router->get('/examine','CarExamineController@index')->name('examiner.index');
    //处理申请页面
    $router->get('/apply/do/1','ApplyController@doApply')->name('apply.do');
    $router->get('/apply/do/2','ApplyController@doApply')->name('apply.do1');
    $router->get('/apply/do/3','ApplyController@doApply')->name('apply.do2');
    //处理申请结果提交
    $router->post('/apply/do/create','ApplyController@store')->name('apply.create');

    $router->get('/msg','MessageController@index')->name('msg.list');

    $router->get('/msg/get','MessageController@getMsg');

});
