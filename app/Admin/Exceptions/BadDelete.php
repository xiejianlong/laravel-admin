<?php

namespace App\Admin\Exceptions;

use Encore\Admin\Admin;

class BadDelete
{
    private $id;
    private $getGridRowName;
    private $resource;
    public function __construct($id)
    {
        $this->id = $id;
        $this->getGridRowName = 'badCar';
        $this->resource = app('request')->getPathInfo();
    }

    public function render(){
        Admin::script($this->script());
        return <<<EOT
<a href="javascript:void(0);" data-id="{$this->id}" class="{$this->getGridRowName}-delete">
    <i class="fa fa-trash"></i>
</a>
EOT;
    }
    /**
     * Script of batch delete action.
     */
    public function script()
    {
        $trans = [
            'delete_confirm' => "确定报销？",
            'confirm'        => "确定",
            'cancel'         => "取消",
        ];

        return <<<EOT

$('.{$this->getGridRowName}-delete').unbind('click').click(function() {

    var id = $(this).data('id');

    swal({
        title: "{$trans['delete_confirm']}",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "{$trans['confirm']}",
        showLoaderOnConfirm: true,
        cancelButtonText: "{$trans['cancel']}",
        preConfirm: function() {
            return new Promise(function(resolve) {
                $.ajax({
                    method: 'post',
                    url: '{$this->resource}/' + id,
                    data: {
                        _method:'delete',
                        _token:LA.token,
                    },
                    success: function (data) {
                        $.pjax.reload('#pjax-container');

                        resolve(data);
                    }
                });
            });
        }
    }).then(function(result) {
        var data = result.value;
        if (typeof data === 'object') {
            if (data.status) {
                swal(data.message, '', 'success');
            } else {
                swal(data.message, '', 'error');
            }
        }
    });
});

EOT;
    }
    public function __toString()
    {
        return $this->render();
    }
}
