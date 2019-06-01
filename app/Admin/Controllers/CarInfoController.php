<?php
namespace App\Admin\Controllers;

use App\Admin\Exceptions\Apply;
use App\Admin\Model\CarInfo;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class CarInfoController extends Controller{
    use HasResourceActions;

    /**
     * @param Content $content
     * @return Content
     */
    public function index(Content $content){
        return $content->header("车辆信息")
            ->description("列表")
            ->body($this->grid());
    }
    /**
     * Show interface.
     *
     * @param mixed   $id
     * @param Content $content
     *
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('车辆信息')
            ->description('详情')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param $id
     *
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('车辆信息')
            ->description('编辑')
            ->body($this->form()->edit($id));
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

    public function grid(){
        $grid = new Grid(new CarInfo());
        $grid->id('ID')->sortable();
        $grid->brand('品牌型号');
        $grid->code('车编号');
        $grid->carType('车型');
        $grid->license('车牌号');
        $grid->status('车辆状态');
        $grid->inspection_t('年检时间');
        $grid->created_at('创建时间');
        $grid->updated_at('更新时间');

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
            $actions->append(new Apply($actions->getKey()));
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
        $form->number('status', '车辆状态')->value(0);
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
        //$form->ignore(['status']);
        return $form;
    }
    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        $car = CarInfo::class;
        $show = new Show($car::findOrFail($id));
            $show->id('ID');
            $show->brand('品牌型号');
            $show->code( '车编号');
            $show->carType('车型');
            $show->license( '车牌号');
            // 添加日期时间选择框
            $show->inspection_t('年检时间');

            return $show;
    }
}