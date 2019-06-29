<?php

namespace App\Admin\Controllers;

use App\Admin\Model\ApplyLogs;
use App\Admin\Service\ApplyService;
use App\Admin\Service\CarExamineService;
use App\Admin\Service\CarInfoService;
use App\Admin\Service\MessageService;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Show;
use Illuminate\Http\Request;

class ApplyController extends Controller
{
    protected $applyService;
    protected $request;
    protected $carExamine;
    protected $carInfoService;
    protected $messageService;
    
    public function __construct(ApplyService $applyService, MessageService $messageService, Request $request, CarExamineService $carExamine, CarInfoService $carInfoService)
    {
        $this->applyService   = $applyService;
        $this->request        = $request;
        $this->carExamine     = $carExamine;
        $this->carInfoService = $carInfoService;
        $this->messageService = $messageService;
    }
    
    public function test()
    {
        $res = $this->applyService->find(2);
        dd($res);
    }
    
    public function doApply(Content $content)
    {
        $content = $content->header('派车单')
                           ->description('处理');
        $content = $content->row(function (Row $row) {
            $row->column(6, function (Column $column) {
                $column->row($this->showApply());
            });
            $row->column(6, $this->form());
        });
        
        return $content;
    }
    
    public function store()
    {
        $exa_id = $this->request->input('exa_id');
        if ($this->request->input('status') == 2) {
            $status = "通过第一次审批";
        }
        if ($this->request->input('status') == 3) {
            $status = "通过第二次审批";
        }
        if ($this->request->input('status') == 4) {
            $status = "通过第三次审批";
        }
        if ($this->request->input('status') == 5) {
            $status = "审批拒绝";
        }
        if ($this->request->input('status') == 6) {
            $status = "已归还";
        }
        $msg    = $this->request->input('msg');
        $e_name = $this->request->input('e_name');
        $res    = $this->applyService->add($exa_id, $status, $msg, $e_name);
        if ($res) {
            //修改对应数据
            $update['status'] = $this->request->input('status');
            $update['e_name'] = Admin::user()->username;
            $update['e_time'] = Carbon::now()
                                      ->toDateTimeString();
            $exa              = $this->carExamine->update($exa_id, $update);
            if ($exa) {
                if(in_array($this->request->input('status'),[2,3,4])){
                    $up['status'] = 2;
                }else{
                    $up['status'] = 0;
                }
                $this->carInfoService->update($exa->car_id, $up);
                //添加申请派车消息提醒
                $userModel = config('admin.database.users_model')::where('username', $exa->name)
                                                                 ->first();
                if ($this->request->input('status') == 2) {
                    $re  = '第一次审批通过了';
                    $msg = Admin::user()->name . $re . '您的派车申请';
                    $this->messageService->add(Admin::user()->id, $userModel->id, '派车单处理通知', $msg);
                }
                if ($this->request->input('status') == 3) {
                    $re  = '第二次审批通过了';
                    $msg = Admin::user()->name . $re . '您的派车申请';
                    $this->messageService->add(Admin::user()->id, $userModel->id, '派车单处理通知', $msg);
                }
                if ($this->request->input('status') == 4) {
                    $re  = '审批全部通过了';
                    $msg = Admin::user()->name . $re . '您的派车申请';
                    $this->messageService->add(Admin::user()->id, $userModel->id, '派车单处理通知', $msg);
                }
                if ($this->request->input('status') == 5) {
                    $re  = '审批拒绝';
                    $msg = Admin::user()->name . $re . '您的派车申请';
                    $this->messageService->add(Admin::user()->id, $userModel->id, '派车单处理通知', $msg);
                }
            }
        }
        
        return redirect('/admin/examine');
    }
    
    
    /**
     * @return Form
     */
    public function showApply()
    {
        $model = $this->carExamine->find($this->request->input('id'));
        
        return new Form($model, function (Form $form) {
            $form->setTitle('派车单：');
            $form->row(function ($row) use ($form) {
                $msg = $form->model()->msg;
                $msg = json_decode($msg, true);
                $row->width(5)
                    ->display('brand', '品牌型号：')
                    ->value($form->model()->brand . $form->model()->carType)
                    ->readonly();
                $row->width(5)
                    ->display('license', '车牌号：')
                    ->value($form->model()->license);
                $row->width(5)
                    ->display('applyName', '申请人：')
                    ->value($msg['applyName']);
                $row->width(5)
                    ->display('useName', '用车人：')
                    ->value($msg['useName']);
                $row->width(5)
                    ->textarea('reason', '用车事由：')
                    ->value($msg['reason'])
                    ->readonly();
                $row->width(5)
                    ->textarea('route', '行车路线：')
                    ->value($msg['route'])
                    ->readonly();;
                $row->width(3)
                    ->display('bknum', '用前公里数：')
                    ->value($msg['bknum'].'.Km')
                    ->readonly();;
                $row->width(3)
                    ->display('aknum', '用后公里数：')
                    ->value($msg['aknum'].'.Km')
                    ->readonly();;
                $row->width(4)
                    ->datetime('useTime', '用车时间：')
                    ->value($msg['useTime'])
                    ->readonly();;
                /*$row->width(4)
                    ->number('knum', '实际行驶公里数：')
                    ->rules('nullable')
                    ->placeholder('实际行驶公里数')
                    ->min(0);*/
                $row->width(10)
                    ->textarea('msg', '备注：')
                    ->value($msg['msg'])
                    ->readonly();;
            });
            $form->footer(function (Form\Footer $footer) {
                $footer->disableCreatingCheck();
                $footer->disableEditingCheck();
                $footer->disableViewCheck();
                // 去掉`提交`按钮
                $footer->disableSubmit();
            });
            $form->tools(function (Form\Tools $tools) {
                // 去掉`列表`按钮
                $tools->disableList();
                // 去掉`删除`按钮
                $tools->disableDelete();
                // 去掉`查看`按钮
                $tools->disableView();
            });
            $form->disableReset();
            $form->setWidth(10, 2);
        });
        $show->brand('品牌型号');
        $show->code('车编号');
        $show->carType('车型');
        $show->license('车牌号');
        $show->divider();
        $show->name('申请人')
             ->as(function ($name) {
                 $userModel = config('admin.database.users_model')::where('username', $name)
                                                                  ->first();
            
                 return (bool)$userModel ? $userModel->name : $name;
             });
        //$show->msg('申请备注');
        // 添加日期时间选择框
        $show->created_at('申请时间');
        
        return $show;
    }
    
    protected function form()
    {
        $form = new Form(new ApplyLogs());
        $form->hidden('exa_id')
             ->value($this->request->input('id'));
        if ($this->request->input('type') == 1) {
            $directors = [
                6 => '归还',
            ];
            $form->textarea('msg', '备注');
        } else {
            if($this->request->input('status') == 1){
                $directors = [
                    2 => '同意，进入下一个审批',
                    4 => '审批结束',
                    5 => '拒绝',
                ];
            }
            if($this->request->input('status') == 2){
                $directors = [
                    3 => '同意，进入下一个审批',
                    4 => '审批结束',
                    5 => '拒绝',
                ];
            }
            if($this->request->input('status') == 3){
                $directors = [
                    4 => '审批结束',
                    5 => '拒绝',
                ];
            }
            $form->textarea('msg', '审批备注');
        }
        $form->select('status', '操作')
             ->options($directors)
             ->rules(['required']);
        $form->hidden('e_name')
             ->value(Admin::user()->name);
        //顶部按钮
        $form->tools(function (Form\Tools $tools) {
            $tools->disableView();
            // 去掉`列表`按钮
            $tools->disableList();
            // 添加一个按钮, 参数可以是字符串, 或者实现了Renderable或Htmlable接口的对象实例
            $tools->add('<a href="/admin/examine" class="btn btn-sm btn-primary"><i class="fa fa-backward"></i>&nbsp;&nbsp;返回</a>');
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