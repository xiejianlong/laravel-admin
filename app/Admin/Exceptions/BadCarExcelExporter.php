<?php

namespace App\Admin\Exceptions;

use Encore\Admin\Grid\Exporters\ExcelExporter;

class BadCarExcelExporter extends ExcelExporter
{
    protected $fileName = '报销车辆信息列表.xlsx';
    
    protected $columns = [
        'id'           => 'ID',
        'Brand'        => '品牌型号',
        'CarType'      => '车型',
        'License'      => '车牌号',
        'Status'       => '报销',
        'Inspection_t' => '年检时间',
        'C_name'       => '创建人',
        'E_name'       => '编辑人',
        'Created_at'   => '创建时间',
        'Updated_at'   => '更新时间',
    ];
    
    public function query()
    {
        $data = $this->getQuery()->get();
        //$data =  parent::query()->get();
        if($data){
            foreach ($data as $k=>$v){
                if($v->getAttribute('status')==3){
                    $v->setAttribute('status','已报销');
                }
            }
        }
        return $data;
    }
}