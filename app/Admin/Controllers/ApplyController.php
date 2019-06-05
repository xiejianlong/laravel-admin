<?php
namespace App\Admin\Controllers;
use App\Admin\Model\ApplyLogs;
use App\Admin\Service\ApplyService;
use App\Admin\Service\CarExamineService;
use App\Admin\Service\CarInfoService;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Show;
use Illuminate\Http\Request;

class ApplyController extends Controller{
    protected $applyService;
    protected $request;
    protected $carExamine;
    protected $carInfoService;
    public function __construct(ApplyService $applyService,Request $request,CarExamineService $carExamine,CarInfoService $carInfoService)
    {
        $this->applyService = $applyService;
        $this->request = $request;
        $this->carExamine = $carExamine;
        $this->carInfoService = $carInfoService;
    }

    public function test(){
        $res = $this->applyService->find(2);
        dd($res);
    }
    public function doApply(Content $content){
        $content = $content->header('派车单')->description('处理');
        $content = $content->row(function (Row $row){
            $row->column(4, function (Column $column) {
                $column->row($this->showApply());
            });
            $row->column(8, $this->form());
        });
        return $content;
    }
    public function store(){
        $exa_id = $this->request->input('exa_id');
        if($this->request->input('status')==2){
            $status = "派车中";
        }
        if($this->request->input('status')==3){
            $status = "已拒绝";
        }
        if($this->request->input('status')==4){
            $status = "已归还";
        }
        $msg = $this->request->input('msg');
        $e_name = $this->request->input('e_name');
        $res = $this->applyService->add($exa_id,$status,$msg,$e_name);
        if($res){
            //修改对应数据
            $update['status'] = $this->request->input('status');
            $update['e_name'] = Admin::user()->username;
            $update['e_time'] = Carbon::now()->toDateTimeString();
            $exa = $this->carExamine->update($exa_id,$update);
            if($exa){
                $up['status'] = $this->request->input('status')==2?2:0;
                $this->carInfoService->update($exa->car_id,$up);
            }
        }
        return redirect('/admin/examine');
    }
    /**
     * @return Show
     */
    public function showApply(){
        $model = $this->carExamine->find($this->request->input('id'));
        $show = new Show($model);
        $show->panel()->style('warning')->title('申请派单信息')->tools(function ($tools) {
            $tools->disableEdit();
            $tools->disableList();
            $tools->disableDelete();
        });
        $show->brand('品牌型号');
        $show->code('车编号');
        $show->carType('车型');
        $show->license('车牌号');
        $show->divider();
        $show->name('申请人')->as(function ($name){
            $userModel = config('admin.database.users_model')::where('username', $name)->first();
            return (bool)$userModel?$userModel->name:$name;
        });
        $show->msg('申请备注');
        // 添加日期时间选择框
        $show->created_at('申请时间');

        return $show;
    }
    protected function form(){
        $form = new Form(new ApplyLogs());
        $form->hidden('exa_id')->value($this->request->input('id'));
        if($this->request->input('type')==1){
            $directors = [
                4 => '归还',
            ];
            $form->textarea('msg','备注');
        }else{
            $directors = [
                2 => '同意',
                3 => '拒绝',
            ];
            $form->textarea('msg','审批备注');
        }
        $form->select('status', '操作')->options($directors)->rules(['required']);
        $form->hidden('e_name')->value(Admin::user()->name);
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
        $form->setAction('create');
        return $form;
    }
}