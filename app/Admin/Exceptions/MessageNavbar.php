<?php
namespace App\Admin\Exceptions;
use App\Admin\Model\MessagesModel;
use Illuminate\Contracts\Support\Renderable;
use Encore\Admin\Admin;

class MessageNavbar implements Renderable {
    protected function script()
    {
        return <<<SCRIPT
        $(function() {
    
});
function(){
    $.ajax({
        method: 'put',
        url: '{$this->resource}/'+selectedRows().join(','),
        data: {
            _token:LA.token,
        },
        success: function (data) {
            $.pjax.reload('#pjax-container');
            toastr.success(data.message);
        }
    });
}
        
SCRIPT;
    }
    public function render()
    {
        //Admin::script($this->script());
        $messages = MessagesModel::with('sender')->inbox()->unread()->orderBy('id', 'desc')->get();
        return view('navbar-menu', compact('messages'))->render();
    }
}