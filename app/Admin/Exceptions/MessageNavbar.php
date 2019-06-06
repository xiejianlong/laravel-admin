<?php
namespace App\Admin\Exceptions;
use App\Admin\Model\MessagesModel;
use Illuminate\Contracts\Support\Renderable;

class MessageNavbar implements Renderable {

    public function render()
    {
        $messages = MessagesModel::with('sender')->inbox()->unread()->orderBy('id', 'desc')->get();
        return view('navbar-menu', compact('messages'))->render();
    }
}