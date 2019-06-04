<?php
namespace App\Admin\Controllers;

use App\Admin\Exceptions\Apply;
use App\Admin\Model\CarInfo;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
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
     * @param $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('车辆信息')
            ->description('编辑')
            ->body($this->form()->edit($id)->ignore(['c_name']));
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
            ->body($this->form()->ignore(['e_name']));
    }

    public function grid(){
        $grid = new Grid(new CarInfo());
        $grid->model()->orderBy('status');
        $grid->id('ID')->sortable();
        $grid->brand('品牌型号');
        $grid->code('车编号');
        $grid->carType('车型');
        $grid->license('车牌号');
        $grid->status('车辆状态')->display(function ($status){
            switch ($status){
                case 0:
                    return "<span class='label label-success'>可使用</span>";
                    break;
                case 1:
                    return "<span class='label label-info'>申请中</span>";
                    break;
                case 2:
                    return "<span class='label label-primary'>派车中</span>";
                    break;
                default:
                    return "<span class='label label-success'>可使用</span>";

            }
        });
        $grid->inspection_t('年检时间')->display(function ($time){
            $t_arr = explode(' ',$time);

            return $t_arr[0];
        });
        $grid->created_at('创建时间');
        $grid->updated_at('更新时间');

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
            if($actions->row->status===0){
                $actions->append(new Apply($actions->getKey()));
            };
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
        $form->text('brand', '品牌型号')->rules('required');
        $form->text('code', '车编号')->rules('required');
        $form->text('carType', '车型')->rules('required');
        $form->text('license', '车牌号')
            ->rules(
                [
                    'required',
                    'regex:'.env('CAR_CODE_PRE'),
                ],
                [
            'regex'=>'车牌号不符合规则'
        ]);
        $form->hidden('status')->value(0);
        $form->hidden('c_name')->value(Admin::user()->username);
        $form->hidden('e_name')->value(Admin::user()->username);
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