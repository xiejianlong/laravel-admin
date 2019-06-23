<?php
namespace App\Admin\Exceptions;
use App\Admin\Model\MessagesModel;
use Illuminate\Contracts\Support\Renderable;
use Encore\Admin\Admin;
use Illuminate\Support\Facades\Request;

class MessageNavbar implements Renderable {
    protected function script()
    {
        $url = env('APP_URL').'/admin/msg/get';
        $query = MessagesModel::with('sender')->inbox()->unread();
       if( \Encore\Admin\Facades\Admin::user()->can('apply.do')){
           $query=  $query->orWhere('to',0);
       };
       if( \Encore\Admin\Facades\Admin::user()->can('car.edit')){
           $query=  $query->orWhere('to',0);
       };
        $messages = $query->get();
        return <<<SCRIPT
window.onload = function () {
      getMsg()
    } 
    function getMsg(){
    setTimeout(getMsg,5*1000);
    $.ajax({
        method: 'get',
        url: "$url",
        data: {
            _token:LA.token,
        },
        success: function (data) {
           loadMsg(data);
        }
    });
    }
    function loadMsg(data){
        var html = '<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-envelope-o"></i>';
        if(data.length>0){
            html += '<span class="label label-success">'+data.length+'</span>';
        }
        html+='</a>';
        html+='<ul class="dropdown-menu">';
        html+='<li class="header">您有'+data.length+'条未读消息</li>';
        html+='<li><ul class="menu">';
        for(var i = 0 ;i<data.length;i++ ){
            html+='<li><a id="redMsg" href="/admin/msg?type=inbox"><div class="pull-left">';
            html+='<img src="'+data[i].sender.avatar+'" class="img-circle" alt="User Image"></div>';
            html+='<h4>'+data[i].title+'<small><i class="fa fa-clock-o"></i>'+data[i].created_at+'</small> </h4>';
            html+='<p>'+data[i].message.substr(0,30)+'</p></a></li>';
        }
        html+='</ul></li><li class="footer"><a href="#">查看全部消息</a></li></ul>';
        $('#navbar-msg').html(html);
    }
SCRIPT;
    }
    public function render()
    {
        Admin::script($this->script());
        $query = MessagesModel::with('sender')->inbox()->unread();
       if( \Encore\Admin\Facades\Admin::user()->can('apply.do')){
           $query=  $query->orWhere('to',0);
       };
        $messages = $query->get();
        return view('navbar-menu', compact('messages'))->render();
    }
}