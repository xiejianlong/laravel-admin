<?php
namespace App\Admin\Exceptions;

use Encore\Admin\Admin;

class DoApply {
     protected $id;
     protected $status;

    public function __construct($id,$status)
    {
        $this->id = $id;
        $this->status = $status;
    }

    protected function script()
    {
        return ;
    }

    protected function render()
    {

        return "<a href='/admin/apply/do/{$this->status}?id={$this->id}&status={$this->status}'>"."<i class='fa fa-paper-plane' title='处理申请'></i>"."</a>";
    }

    public function __toString()
    {
        return $this->render();
    }
}
