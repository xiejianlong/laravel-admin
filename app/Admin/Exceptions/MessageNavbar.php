<?php
namespace App\Admin\Exceptions;
use Illuminate\View\View;

class MessageNavbar {
    public function __construct()
    {
    }
    protected function script(){

    }

    protected function render()
    {
        return '<li class="dropdown messages-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-envelope-o"></i>
                        <span class="label label-success">0</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="header">你有 1条新消息</li>
                        <li>
                            <!-- inner menu: contains the actual data -->
                            <ul class="menu">
                                    <li><!-- start message -->
                                        <a href="#">
                                            <div class="pull-left">
                                                <img src="{{$message->sender->avatar}}" class="img-circle" alt="User Image">
                                            </div>
                                            <h4>
                                                {{$message->title}}
                                                <small><i class="fa fa-clock-o"></i> {{ $message->created_at->diffForHumans() }}</small>
                                            </h4>
                                            <p>{{ str_limit($message->message, 30) }}</p>
                                        </a>
                                    </li>
                            </ul>
                        </li>
                        <li class="footer"><a href="#">查看所有</a></li>
                    </ul>
                </li>';
    }

    public function __toString()
    {
        return $this->render();
    }
}