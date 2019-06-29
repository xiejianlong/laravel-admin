<?php
namespace App\Admin\Exceptions;
use App\Admin\Model\MessagesModel;
use Illuminate\Contracts\Support\Renderable;
use Encore\Admin\Admin;
use Illuminate\Support\Facades\Request;

class MessageNavbar implements Renderable {
    public function render()
    {
        //Admin::script($this->script());
        $query = MessagesModel::with('sender')->inbox()->unread();
       if( \Encore\Admin\Facades\Admin::user()->can('apply.do')){
           $query=  $query->orWhere('to',0);
       };
        $messages = $query->get();
        return view('navbar-menu', compact('messages'))->render();
    }
}