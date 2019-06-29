<?php

namespace App\Admin\Controllers;

use App\Admin\Exceptions\DoApply;
use App\Admin\Model\ApplyLogs;
use App\Admin\Model\CarExamine;
use App\Admin\Model\CarInfo;
use App\Admin\Service\MessageService;
use App\Http\Controllers\Controller;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Auth\Database\Permission;
use Encore\Admin\Auth\Database\Role;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;
use function foo\func;
use Illuminate\Http\Request;
use Illuminate\View\View;

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
    protected $messageService;
    
    public function __construct(Request $request, CarInfo $carInfo, MessageService $messageService, CarExamine $carExamine, ApplyLogs $logs, Administrator $userModel)
    {
        $this->request        = $request;
        $this->carModel       = $carInfo;
        $this->carExamine     = $carExamine;
        $this->logs           = $logs;
        $this->userModel      = $userModel;
        $this->messageService = $messageService;
        $this->user           = Admin::user();
    }
    
    /**
     * 列表页
     *
     * @param Content $content
     *
     * @return Content
     */
    public function index(Content $content)
    {
        return $content->header("派车信息")
                       ->description("列表")
                       ->body($this->grid());
    }
    
    /**
     * 申请派车跳转填写页面
     *
     * @param Content $content
     *
     * @return Content
     */
    public function create(Content $content)
    {
        $this->carInfo = $this->carModel->findOrFail($this->request->input('id'));
        if ($this->carInfo->status == 0) {
            return $content->header('申请派车')
                           ->description('填写申请单')
                           ->row(function (Row $row) {
                               $row->column(4, function (Column $column) {
                                   $column->row($this->showCarInfo());
                               });
                               $row->column(8, $this->form());
                           });
        } else {
            return redirect('/admin/car');
        }
    }
    
    /**
     * @param Request $request
     */
    public function store(Request $request)
    {
        $examine          = $this->carExamine;
        $examine->car_id  = $request->input('car_id');
        $examine->brand   = $request->input('brand');
        $examine->code    = $request->input('code');
        $examine->carType = $request->input('carType');
        $examine->license = $request->input('license');
        $examine->status  = 1;
        $examine->name    = $request->input('name');
        $msg['applyName'] = $request->input('applyName');
        $msg['useName'] = $request->input('useName');
        $msg['reason'] = $request->input('reason');
        $msg['route'] = $request->input('route');
        $msg['bknum'] = $request->input('bknum');
        $msg['aknum'] = $request->input('aknum');
        $msg['knum'] = 0;
        $msg['useTime'] = $request->input('useTime');
        $msg['msg'] = $request->input('msg');
        
        $examine->msg     = json_encode($msg);
        if ($examine->save()) {
            $carInfo         = $this->carModel::find($request->input('car_id'));
            $carInfo->status = 1;
            $carInfo->save();
            
            $user = Admin::user();
            //添加操作日志
            $logModel         = $this->logs;
            $logModel->exa_id = $examine->id;
            $logModel->e_name = $user->name;
            $logModel->status = "申请派车";
            $logModel->msg    = json_encode($msg);
            $logModel->save();
            //添加申请派车消息提醒
            $msg = $user->name . '申请派车【' . $examine->brand . '】车牌号：' . $examine->license . '请前往【车辆管理->审核调度】处理';
            $userid = $this->getUseridByPre('apply.do');
            foreach ($userid as $id){
                $this->messageService->add($user->id, $id, '申请派车消息', $msg);
            }
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
        //判断权限（如果该用户权限可以操作申请的派车单）
        if ($user->can('examiner.create')) {
        } else {
            $grid->model()
                 ->where('name', $user->username);
        }
        $grid->model()
             ->where('status', '>', '0');
        $grid->model()
             ->orderBy('status');
        $grid->id('ID')
             ->sortable();
        $grid->brand('品牌型号');
        $grid->code('车编号');
        $grid->carType('车型');
        $grid->license('车牌号');
        $grid->status('车辆状态')
             ->display(function ($status) {
                 switch ($status) {
                     case 0:
                         return "<span class='label label-success'>可使用</span>";
                         break;
                     case 1:
                         return "<span class='label label-info'>申请中</span>";
                         break;
                     case 2:
                         return "<span class='label label-primary'>审批中</span>";
                         break;
                     case 3:
                         return "<span class='label label-primary'>审批中</span>";
                         break;
                     case 4:
                         return "<span class='label label-primary'>派车中</span>";
                         break;
                     case 5:
                         return "<span class='label label-warning'>已拒绝</span>";
                         break;
                     case 6:
                         return "<span class='label label-default'>已归还</span>";
                         break;
                     default:
                         return "<span class='label label-info'>申请中</span>";
                 }
             })
             ->modal('操作日志', function ($model) {
                 $field    = [
                     'e_name',
                     'status',
                     'created_at',
                 ];
                 $logModel = ApplyLogs::where('exa_id', $model->id)
                                      ->orderBy('created_at', 'desc')
                                      ->get($field);
            
                 return new Table([
                     '操作人',
                     '动作',
                     '操作时间',
                 ], $logModel->toArray(), [
                     'primary',
                     'info',
                     'danger',
                     'warning',
                 ]);
             });
        $grid->name('申请人')
             ->display(function ($userId) {
                 $userModel = config('admin.database.users_model')::where('username', $userId)
                                                                  ->first();
            
                 return $userModel ? $userModel->name : $userId;
             });
        $grid->column('msg', '申请备注')->display(function ($msg){
            if(is_string($msg)){
                $msg = json_decode($msg,true);
            }
            return "<a href='#' data-toggle=\"modal\" data-target=\"#paichedanModal\" data-applyName='{$msg["applyName"]}'data-useName='{$msg["useName"]}' data-reason='{$msg["reason"]}'data-route='{$msg["route"]}'data-aknum='{$msg["aknum"]}'data-useTime='{$msg["useTime"]}'data-bknum='{$msg["bknum"]}'data-msg='{$msg["msg"]}'>派车单</a>";
        });
        $grid->e_name('审批人')
             ->display(function ($userId) {
                 $userModel = config('admin.database.users_model')::where('username', $userId)
                                                                  ->first();
            
                 return $userModel ? $userModel->name : $userId;
             });;
        $grid->e_time('审批时间');
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();
            if ($actions->row->status == 1 && Admin::user()
                                                   ->can('apply.do')) {//处理申请派车//需要有权限的用户
                $actions->append(new DoApply($actions->getKey(),$actions->row->status));
            }
            if ($actions->row->status == 2 && Admin::user()
                                                   ->can('apply.do1')) {//处理申请派车//需要有权限的用户
                $actions->append(new DoApply($actions->getKey(),$actions->row->status));
            }
            if ($actions->row->status == 3 && Admin::user()
                                                   ->can('apply.do2')) {//处理申请派车//需要有权限的用户
                $actions->append(new DoApply($actions->getKey(),$actions->row->status));
            }
            if ($actions->row->status == 5 && Admin::user()->username == $actions->row->name) {//派车中 需要去归还
                $actions->append("<a href='/admin/apply/do?id={$actions->row->id}&type=1'>" . "<i class='fa fa-mail-reply-all' title='归还车辆'></i>" . "</a>");
            }
        });
        
        $grid->tools(function (Grid\Tools $tools) {
            $tools->batch(function (Grid\Tools\BatchActions $actions) {
                $actions->disableDelete();
            });
            $tools->append($this->getDetail());
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
        $grid->disableExport();
        $grid->disableRowSelector();
        
        return $grid;
    }
    
    protected function form()
    {
        return new Form($this->carExamine, function (Form $form) {
            $form->setTitle('派车单：');
            $form->display('id', 'ID');
            $form->row(function ($row) use ($form) {
                $row->hidden('car_id')
                     ->value($this->carInfo->id);
                $row->hidden('brand')
                     ->value($this->carInfo->brand);
                $row->hidden('code')
                     ->value($this->carInfo->code);
                $row->hidden('carType')
                     ->value($this->carInfo->carType);
                $row->hidden('license')
                     ->value($this->carInfo->license);
                $row->hidden('name')
                     ->value(Admin::user()->username);
                $row->width(5)
                    ->text('applyName', '申请人：')
                    ->rules('required')
                    ->placeholder('请填写申请人');
                $row->width(5)
                    ->text('useName', '用车人：')
                    ->rules('required')
                    ->placeholder('请填写用车人');
                $row->width(5)
                    ->textarea('reason', '用车事由：')
                    ->rules('required')
                    ->placeholder('请填写用车事由');
                $row->width(5)
                    ->textarea('route', '行车路线：')
                    ->rules('required')
                    ->placeholder('请填写行车路线');
                $row->width(4)
                    ->number('bknum', '用前公里数：')
                    ->rules('required')
                    ->placeholder('用前公里数')
                    ->min(0);
                $row->width(3)
                    ->number('aknum', '用后公里数：')
                    ->rules('nullable')
                    ->placeholder('用后公里数')
                    ->min(0);
                $row->width(4)
                    ->datetime('useTime', '用车时间：')
                    ->rules('required');
                /*$row->width(4)
                    ->number('knum', '实际行驶公里数：')
                    ->rules('nullable')
                    ->placeholder('实际行驶公里数')
                    ->min(0);*/
                $row->width(10)
                    ->textarea('msg', '备注：')
                    ->rules('nullable')
                    ->placeholder('用车备注信息');
            });
            $form->footer(function (Form\Footer $footer) {
                $footer->disableCreatingCheck();
                $footer->disableEditingCheck();
                $footer->disableViewCheck();
            });
            $form->tools(function (Form\Tools $tools) {
                // 去掉`列表`按钮
                $tools->disableList();
                // 去掉`删除`按钮
                $tools->disableDelete();
                // 去掉`查看`按钮
                $tools->disableView();
        
                // 添加一个按钮, 参数可以是字符串, 或者实现了Renderable或Htmlable接口的对象实例
                $tools->add('<a href="/admin/car" class="btn btn-sm btn-primary"><i class="fa fa-backward"></i>&nbsp;&nbsp;返回</a>');
            });
            $form->disableReset();
            $form->setWidth(10, 2);
        });
    }
    
    protected function showCarInfo()
    {
        $show = new Show($this->carInfo);
        $show->panel()
             ->style('warning')
             ->title('车辆信息')
             ->tools(function ($tools) {
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
    
    /**
     * @param $pre
     *
     * @return array
     */
    protected function getUseridByPre($pre){
        $role = Permission::with('roles')->where('slug',$pre)->get()->toArray();
        $ids = [];
        if($role&&!empty($role)){
            foreach ($role as $k=>$v){
                if($v['roles']&&!empty($v['roles'])){
                    foreach ($v['roles']as $k1=>$v1){
                        $users = Role::with('administrators')->where('id',$v1['id'])->first()->toArray();
                        if(is_array($users)&&!empty($users)){
                            $ids = array_merge($ids,array_column($users['administrators'],'id'));
                        }
                    }
                }
            }
        }
        return $ids;
    }
    
    protected function getDetail(){
        $script = <<<SCRIPT

$('#paichedanModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var applyName = button.data('applyname');
    var useName = button.data('usename');
    var reason = button.data('reason');
    var route = button.data('route');
    var aknum = button.data('aknum');
    var bknum = button.data('bknum');
    var useTime = button.data('usetime');
    var msg = button.data('msg');

    var modal = $(this);
    console.log(applyName);
    console.log(useName);
    console.log(useTime);
    modal.find('.modal-body #message-applyName').val(applyName);
    modal.find('.modal-body #message-useName').val(useName);
    modal.find('.modal-body #message-reason').val(reason);
    modal.find('.modal-body #message-route').val(route);
    modal.find('.modal-body #message-aknum').val(aknum);
    modal.find('.modal-body #message-bknum').val(bknum);
    modal.find('.modal-body #message-useTime').val(useTime);
    modal.find('.modal-body #message-msg').val(msg);

}).on('hide.bs.modal', function (event) {
$.pjax.reload('#pjax-container');
});
SCRIPT;
    
        Admin::script($script);
        return <<<'MODAL'
<div class="modal fade" id="paichedanModal" tabindex="-1" role="dialog" aria-labelledby="paichedanModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="paichedanModalLabel">派车单</h4>
      </div>
      <div class="modal-body">
        <form>
          <div class="form-group" style="float: left;width: 50%">
            <label for="message-applyName" class="control-label">申请人:</label>
            <input type="text" class="form-control" id="message-applyName">
          </div>
          <div class="form-group" style="float: left;width: 50%">
            <label for="message-useName" class="control-label">用车人:</label>
            <input type="text" class="form-control" id="message-useName">
          </div>
          <div class="form-group" >
            <label for="message-reason" class="control-label">用车事由:</label>
            <textarea  class="form-control" id="message-reason"></textarea>
          </div>
          <div class="form-group">
            <label for="message-route" class="control-label">行车路径:</label>
            <textarea  class="form-control" id="message-route"></textarea>
          </div>
          <div class="form-group"style="float: left;width: 50%">
            <label for="message-bknum" class="control-label">用车前公里数:</label>
            <input type="number" class="form-control" id="message-bknum">
          </div>
          <div class="form-group"style="float: left;width: 50%">
            <label for="message-aknum" class="control-label">用车后公里数:</label>
            <input type="number" class="form-control" id="message-aknum">
          </div>
          <div class="form-group">
            <label for="message-useTime" class="control-label">用车时间:</label>
            <input type="text" class="form-control" id="message-useTime">
          </div>
          <div class="form-group">
            <label for="message-msg" class="control-label">备注:</label>
            <textarea  class="form-control" id="message-msg"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
      </div>
    </div>
  </div>
</div>
MODAL;
    }
}