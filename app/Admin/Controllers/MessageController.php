<?php
namespace App\Admin\Controllers;
use App\Admin\Exceptions\MessageType;
use App\Admin\Model\MessagesModel;
use App\Admin\Service\MessageService;
use Carbon\Carbon;
use Encore\Admin\Controllers\ModelForm;
use App\Http\Controllers\Controller;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class MessageController extends Controller{
    use ModelForm;
    protected $messageService;
    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
    }

    public function index(Content $content){
        return $content->header("消息")->description("列表")->body($this->grid());
    }

    public function grid()
    {
        return Admin::grid(MessagesModel::class, function (Grid $grid) {
            $type = request()->get('type', 'inbox');

            $grid->model()->with('sender')->orderBy('id', 'desc')->{$type}();
            if(Admin::user()->can('apply.do') &&$type=='inbox'){
                $grid->model()->orWhere('to',0);
            }
            if(Admin::user()->can('car.edit') &&$type=='inbox'){
                $grid->model()->orWhere('to',0);
            }
            $grid->id('ID')->sortable();

            if($type=='inbox'){
                $grid->sender()->name('发送者');
            }else{
                $grid->receiver()->name('接收者');
            }
            $grid->title('标题')->display(function ($title) {
                return "<a href='#' data-toggle=\"modal\" data-target=\"#messageModal\" data-id='{$this->id}' data-from='{$this->sender['name']}' data-title='{$this->title}' data-message='{$this->message}' data-time='{$this->created_at}'>$title</a>";
            });
            $grid->message('消息内容')->limit(40);

            $grid->created_at('发送时间')->display(function ($time) {
                return Carbon::parse($time)->diffForHumans();
            });

            $grid->filter(function ($filter) {
                $filter->disableIdFilter();
                $filter->like('title');
                $filter->like('message');

                $filter->between('created_at')->datetime();
            });

            $grid->tools(function (Grid\Tools $tools) {
                $tools->append(new MessageType());
                $tools->batch(function (Grid\Tools\BatchActions $actions) {
                    $actions->disableDelete();
                });
                $tools->append($this->messageModal());
            });

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->disableEdit();
                $actions->disableDelete();
                $actions->disableView();
                /*$url = $actions->getResource().'/create?';

                $url .= http_build_query([
                    'title' => 'Re:'.$actions->row->title,
                    'to'    => $actions->row->from,
                ]);

                $actions->prepend("<a class=\"btn btn-xs\" href=\"$url\"><i class=\"fa fa-reply\"></i></a>");*/
            });

            /*if ($type == 'inbox') {
                $grid->rows(function (Grid\Row $row) {
                    if (is_null($row->read_at)) {
                        $row->setAttributes(['style' => 'font-weight: 700;']);
                    }
                });

                $grid->tools(function ($tools) {
                    $tools->batch(function (Grid\Tools\BatchActions $batch) {
                        $batch->add('标记已读', new MarkAsRead());
                    });
                });
            }*/
            $grid->disableCreateButton();
            $grid->disableActions();
            $grid->disableExport();
            $grid->disableRowSelector();
            $grid->disableColumnSelector();
        });
    }
    protected function messageModal()
    {
        $path = trim(request()->path(), '/');

        $script = <<<SCRIPT

$('#messageModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var from = button.data('from');
    var title = button.data('title');
    var message = button.data('message');
    var time = button.data('time');

    var modal = $(this);
    modal.find('.modal-title').text(title);
    modal.find('.modal-body #message-from').val(from);
    modal.find('.modal-body #message-title').val(title);
    modal.find('.modal-body #message-text').val(message);
    modal.find('.modal-body #message-time').val(time);

    $.ajax({
        method: 'put',
        url: '/{$path}/' + button.data('id'),
       
    });

}).on('hide.bs.modal', function (event) {
    $.pjax.reload('#pjax-container');
});

SCRIPT;

        Admin::script($script);

        return <<<'MODAL'
<div class="modal fade" id="messageModal" tabindex="-1" role="dialog" aria-labelledby="messageModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="messageModalLabel">新消息</h4>
      </div>
      <div class="modal-body">
        <form>
          <div class="form-group">
            <label for="message-from" class="control-label">发送者:</label>
            <input type="text" class="form-control" id="message-from">
          </div>
          <div class="form-group">
            <label for="message-title" class="control-label">标题:</label>
            <input type="text" class="form-control" id="message-title">
          </div>
          <div class="form-group">
            <label for="message-text" class="control-label">内容:</label>
            <textarea class="form-control" id="message-text" rows=8></textarea>
          </div>
          <div class="form-group">
            <label for="message-time" class="control-label">时间:</label>
            <input type="text" class="form-control" id="message-time">
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
    public function getMsg(){
        $query = MessagesModel::with('sender')->inbox()->unread();
       if( Admin::user()->can('apply.do')){
           $query=  $query->orWhere('to',0)->whereNull('read_at');
       };
       if( Admin::user()->can('car.edit')){
           $query=  $query->orWhere('to',0)->whereNull('read_at');
       };
        $messages = $query->get();
        $res = [];
        foreach ( $messages as $k=>$v){
            $data = $v->toArray();
            $data['created_at'] = $v->created_at->diffForHumans();
            if(isset($v->sender->avatar)){
                $data['sender']['avatar'] = ($v->sender->avatar).'?_token='.request()->get('_token');
            }
            $data['created_at'] = $v->created_at->diffForHumans();
            $res[] = $data;
            
        }
        //return urlencode('http://car.admin.com/storage/images/c9fe71d5795147fa5ea74ef7b095c160.jpg');
        return $res;
    }
}