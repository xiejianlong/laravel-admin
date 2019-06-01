<?php

namespace App\Admin\Controllers;

use App\Admin\Model\CarExamine;
use App\Admin\Model\CarInfo;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpKernel\DependencyInjection\LazyLoadingFragmentHandler;

class CarExamineController extends Controller
{
    use HasResourceActions;
    protected $carInfo;
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param Content $content
     * @return Content
     */
    public function index( Content $content )
    {
        return $content->header("待审核")->description("列表")->body($this->grid());
    }

    /**
     * @return Grid
     */
    public function grid()
    {
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

        $grid->actions(function ( Grid\Displayers\Actions $actions ) {
            $actions->disableView();
        });

        $grid->tools(function ( Grid\Tools $tools ) {
            $tools->batch(function ( Grid\Tools\BatchActions $actions ) {
                $actions->disableDelete();
            });
        });
        $grid->filter(function ( $filter ) {
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
     * 申请派车跳转填写页面
     * @param         $id
     * @param Content $content
     * @return Content
     */
    public function create($id, Content $content )
    {
        $carModel = new CarInfo();
        $this->carInfo = $carModel->findOrFail($id);

        return $content->header('申请派车')->description('填写申请单')->row(function ( Row $row ) {
                $row->column(4, function ( Column $column ) {
                    $column->row($this->showCarInfo());
                });
                $row->column(8, $this->form());
            });
    }

    public function createInfo(Request $request){
       $examine = new CarExamine();
       $examine->car_id = $request->input('car_id');
       $examine->brand = $request->input('brand');
       $examine->code = $request->input('code');
       $examine->carType = $request->input('carType');
       $examine->license = $request->input('license');
       $examine->status = $request->input('status');
       $examine->name = $request->input('name');
       $examine->msg = $request->input('msg');
       $examine->save();
       return new Redirect('/admin/examine');
    }

    protected function form()
    {
        $form = new Form(new CarExamine());
        // 显示记录id
        $form->display('id', 'ID');
        $form->hidden('car_id')->value($this->carInfo->id);
        $form->hidden('brand')->value($this->carInfo->brand);
        $form->hidden('code')->value($this->carInfo->code);
        $form->hidden('carType')->value($this->carInfo->carType);
        $form->hidden('license')->value($this->carInfo->license);
        $form->hidden('status')->value($this->carInfo->status);
        $form->hidden('name')->value(Admin::user()->name);
        // 添加text类型的input框
        $form->textarea('msg', '申请备注')->rules('nullable')->placeholder('请填写申请备注');
        //顶部按钮
        $form->tools(function ( Form\Tools $tools ) {
            $tools->disableView();
        });
        //底部按钮
        $form->footer(function ( Form\Footer $footer ) {
            $footer->disableCreatingCheck();
            $footer->disableEditingCheck();
            $footer->disableViewCheck();
        });
        $form->disableReset();

        //保存后回调
        $form->saved(function ( Form $form ) {
            redirect('/admin/examine');//跳转到申请列表也
        });

        $form->setAction('admin/examine/create/'.$this->carInfo->id.'');
        return $form;
    }

    protected function showCarInfo()
    {
        $show = new Show($this->carInfo);
        $show->panel()->title('车辆信息')->tools(function ( $tools ) {
                $tools->disableEdit();
                $tools->disableList();
                $tools->disableDelete();
            });
        $show->id('ID');
        $show->brand('品牌型号');
        $show->code('车编号');
        $show->carType('车型');
        $show->license('车牌号');
        // 添加日期时间选择框
        $show->inspection_t('年检时间');

        return $show;
    }
}