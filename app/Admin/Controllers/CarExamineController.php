<?php

namespace App\Admin\Controllers;

use App\Admin\Exceptions\DoApply;
use App\Admin\Model\ApplyLogs;
use App\Admin\Model\CarExamine;
use App\Admin\Model\CarInfo;
use App\Http\Controllers\Controller;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;
use Illuminate\Http\Request;

class CarExamineController extends Controller
{
    use HasResourceActions;
    protected $carInfo;
    protected $request;
    protected $carModel;
    protected $carExamine;
    protected $logs;
    protected $userModel;
    protected $user;

    public function __construct(Request $request, CarInfo $carInfo, CarExamine $carExamine, ApplyLogs $logs, Administrator $userModel)
    {
        $this->request = $request;
        $this->carModel = $carInfo;
        $this->carExamine = $carExamine;
        $this->logs = $logs;
        $this->userModel = $userModel;
        $this->user = Admin::user();
    }

    /**
     * 列表页
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content->header("派车信息")->description("列表")->body($this->grid());
    }

    /**
     * 申请派车跳转填写页面
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        $this->carInfo = $this->carModel->findOrFail($this->request->input('id'));
        return $content->header('申请派车')->description('填写申请单')->row(function (Row $row) {
            $row->column(4, function (Column $column) {
                $column->row($this->showCarInfo());
            });
            $row->column(8, $this->form());
        });
    }

    /**
     * @param Request $request
     */
    public function store(Request $request)
    {
        $examine = $this->carExamine;
        $examine->car_id = $request->input('car_id');
        $examine->brand = $request->input('brand');
        $examine->code = $request->input('code');
        $examine->carType = $request->input('carType');
        $examine->license = $request->input('license');
        $examine->status = 1;
        $examine->name = $request->input('name');
        $examine->msg = $request->input('msg');
        if ($examine->save()) {
            $carInfo = $this->carModel::find($request->input('car_id'));
            $carInfo->status = 1;
            $carInfo->save();

            $user = Admin::user();
            //添加操作日志
            $logModel = $this->logs;
            $logModel->exa_id = $examine->id;
            $logModel->e_name = $user->name;
            $logModel->status = "申请派车";
            $logModel->msg = $request->input('msg');
            $logModel->save();
        }
        redirect('/admin/examine');;
    }

    /**
     * @return Grid
     */
    protected function grid()
    {
        $user = Admin::user();
        $grid = new Grid($this->carExamine);
        $grid->model()->where('name', $user->username);
        $grid->model()->where('status', '>', '0');
        $grid->model()->orderBy('status');
        $grid->id('ID')->sortable();
        $grid->brand('品牌型号');
        $grid->code('车编号');
        $grid->carType('车型');
        $grid->license('车牌号');
        $grid->status('车辆状态')->display(function ($status) {
            switch ($status) {
                case 0:
                    return "<span class='label label-success'>可使用</span>";
                    break;
                case 1:
                    return "<span class='label label-info'>申请中</span>";
                    break;
                case 2:
                    return "<span class='label label-primary'>派车中</span>";
                    break;
                case 3:
                    return "<span class='label label-warning'>已拒绝</span>";
                    break;
                case 4:
                    return "<span class='label label-primary'>已归还</span>";
                    break;
                default:
                    return "<span class='label label-info'>申请中</span>";
            }
        })->modal('操作日志', function ($model) {
            $field = ['e_name', 'status', 'msg', 'created_at'];
            $logModel = ApplyLogs::where('exa_id', $model->id)->orderBy('created_at','desc')->get($field);
            return new Table(['操作人', '动作', '备注', '操作时间'], $logModel->toArray(),['primary','info','danger','warning']);
        });
        $grid->name('申请人')->display(function ($userId) {
            $userModel = config('admin.database.users_model')::where('username', $userId)->first();
            return $userModel ? $userModel->name : $userId;
        });
        $grid->column('msg', '申请备注')->limit(40);
        $grid->e_name('审批人')->display(function ($userId) {
            $userModel = config('admin.database.users_model')::where('username', $userId)->first();
            return $userModel ? $userModel->name : $userId;
        });;
        $grid->e_time('审批时间');
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();
            if($actions->row->status==1){
                $actions->append(new DoApply($actions->getKey()));
            }
            if($actions->row->status==2&&Admin::user()->username==$actions->row->name){//派车中 需要去归还
                $actions->append("<a href='/admin/apply/do?id={$actions->row->id}&type=1'>"."<i class='fa fa-mail-reply-all' title='归还车辆'></i>"."</a>");
            }
        });

        $grid->tools(function (Grid\Tools $tools) {
            $tools->batch(function (Grid\Tools\BatchActions $actions) {
                $actions->disableDelete();
            });
        });
        $grid->filter(function ($filter) {
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            // 在这里添加字段过滤器
            $filter->like('brand', '品牌型号');
            $filter->equal('name', '申请人');
            $filter->like('license', '车牌号');
        });
        $grid->disableCreateButton();
        return $grid;
    }

    protected function form()
    {
        $form = new Form($this->carExamine);
        // 显示记录id
        $form->display('id', 'ID');
        $form->hidden('car_id')->value($this->carInfo->id);
        $form->hidden('brand')->value($this->carInfo->brand);
        $form->hidden('code')->value($this->carInfo->code);
        $form->hidden('carType')->value($this->carInfo->carType);
        $form->hidden('license')->value($this->carInfo->license);
        $form->hidden('name')->value(Admin::user()->username);
        // 添加text类型的input框
        $form->textarea('msg', '申请备注')->rules('nullable')->placeholder('请填写申请备注');
        //顶部按钮
        $form->tools(function (Form\Tools $tools) {
            $tools->disableView();
        });
        //底部按钮
        $form->footer(function (Form\Footer $footer) {
            $footer->disableCreatingCheck();
            $footer->disableEditingCheck();
            $footer->disableViewCheck();
        });
        $form->disableReset();
        return $form;
    }

    protected function showCarInfo()
    {
        $show = new Show($this->carInfo);
        $show->panel()->style('warning')->title('车辆信息')->tools(function ($tools) {
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