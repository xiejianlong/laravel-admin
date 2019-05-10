<?php
namespace App\Admin\Controllers;

use App\Admin\Model\CarInfo;
use App\Http\Controllers\Controller;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use function foo\func;

class CarInfoController extends Controller{
    public function index(Content $content){
        return $content->header("车辆信息")->description("列表")
            ->body($this->grid());
    }
    public function grid(){
        $grid = new Grid(new CarInfo());
        $grid->id('ID')->sortable();
        $grid->brand('品牌型号');
        $grid->code('车编号');
        $grid->carType('车型');
        $grid->license('车牌号');
        $grid->status('车辆状态');
        $grid->inspection_t('年检时间');
        $grid->created_at(trans('admin.created_at'));
        $grid->updated_at(trans('admin.updated_at'));

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            if ($actions->getKey() == 1) {
                $actions->disableDelete();
            }
        });

        $grid->tools(function (Grid\Tools $tools) {
            $tools->batch(function (Grid\Tools\BatchActions $actions) {
                $actions->disableDelete();
            });
        });

        return $grid;
    }

    /**
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('车辆信息')
            ->description('添加车辆信息')
            ->body($this->form());
    }

    /**
     * @return Form
     */
    public function form(){
        $form = new Form(new CarInfo());
        // 显示记录id
        $form->display('id', 'ID');
        // 添加text类型的input框
        $form->text('brand', '品牌型号');
        $form->text('code', '车编号');
        $form->text('carType', '车型');
        $form->text('license', '车牌号');
        // 添加日期时间选择框
        $form->datetime('inspection_t', '年检时间');
        $form->tools(function (Form\Tools $tools){
           $tools->disableView();
        });
        $form->footer(function ( Form\Footer $footer){
           $footer-> disableCreatingCheck();
           $footer->disableEditingCheck();
           $footer->disableViewCheck();
        });
        return $form;
    }
}