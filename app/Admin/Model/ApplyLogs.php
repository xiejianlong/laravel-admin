<?php
namespace App\Admin\Model;

use Illuminate\Database\Eloquent\Model;
class ApplyLogs extends Model{
    /**
     * 与模型关联的数据表
     *
     * @var string
     */
    protected $table = 'apply_logs';

    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = ['id','created_at','updated_at'];
}