<?php
namespace App\Admin\Exceptions;

use Encore\Admin\Admin;

class Apply {
     protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    protected function script()
    {
        return ;
    }

    protected function render()
    {
        //Admin::script($this->script());

        //return "<a class='fa-paper-plane' href='/admin/examine/{$this->id}'></a>";
        return "<a href='/admin/examine/create/{$this->id}'>"."<i class='fa fa-paper-plane-o' title='申请车辆'></i>"."</a>";
    }

    public function __toString()
    {
        return $this->render();
    }
}
