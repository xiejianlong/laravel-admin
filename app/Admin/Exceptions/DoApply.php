<?php
namespace App\Admin\Exceptions;

use Encore\Admin\Admin;

class DoApply {
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

        return "<a href='/admin/apply/do?id={$this->id}'>"."<i class='fa fa-paper-plane' title='处理申请'></i>"."</a>";
    }

    public function __toString()
    {
        return $this->render();
    }
}
