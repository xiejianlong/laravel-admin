<?php
namespace App\Admin\Controllers;

use App\Admin\Model\CarExamine;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class CarExamineController extends Controller{
    use HasResourceActions;

    /**
     * @param Content $content
     * @return Content
     */
    public function index(Content $content){
        return $content->header("待审核")
            ->description("列表")
            ->body($this->grid());
    }

    /**
     * @return Grid
     */
    public function grid(){
        $grid = new Grid(new CarExamine());
        $grid->id('ID')->sortable();
        $grid->name('申请人');
        $grid->e_name('审批人');
        $grid->e_time('审批时间');
        $grid->brand('品牌型号');
        $grid->code('车编号');
        $grid->carType('车型');
        $grid->license('车牌号');
        $grid->status('车辆状态');
        //$grid->created_at('');
        //$grid->updated_at(trans('admin.updated_at'));

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
        });

        $grid->tools(function (Grid\Tools $tools) {
            $tools->batch(function (Grid\Tools\BatchActions $actions) {
                $actions->disableDelete();
            });
        });
        $grid->filter(function($filter){
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            // 在这里添加字段过滤器
            $filter->like('brand', '品牌型号');
            $filter->equal('status', '车辆状态');
            $filter->like('license', '车牌号');
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
}