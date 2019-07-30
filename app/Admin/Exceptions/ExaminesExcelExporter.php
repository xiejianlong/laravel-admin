<?php

namespace App\Admin\Exceptions;

use Encore\Admin\Grid\Exporters\ExcelExporter;

class ExaminesExcelExporter extends ExcelExporter
{
    protected $fileName = '派车申请.xlsx';
    
    protected $columns = [
        'id'           => 'ID',
        'Brand'        => '品牌型号',
        'CarType'      => '车型',
        'License'      => '车牌号',
        'Status'       => '车辆状态',
        'Inspection_t' => '年检时间',
        'C_name'       => '创建人',
        'E_name'       => '编辑人',
        'Created_at'   => '创建时间',
        'Updated_at'   => '更新时间',
    ];
}