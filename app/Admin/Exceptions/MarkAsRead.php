<?php

namespace App\Admin\Exceptions;

use Encore\Admin\Grid\Tools\BatchAction;

class MarkAsRead extends BatchAction
{
    public function script()
    {
        return <<<EOT
        
$('#redMsg').on('click', function() {

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
});

EOT;
    }
}
