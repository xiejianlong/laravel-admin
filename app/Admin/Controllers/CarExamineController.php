<?php
namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Encore\Admin\Layout\Content;

class CarExamineController extends Controller{
    public function index(Content $content){
        return $content->header("待审核")->description("列表")
            ;
    }
}