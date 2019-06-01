<?php
namespace App\Admin\Model;

use Illuminate\Database\Eloquent\Model;

class CarExamine extends Model{
    /**
     * 与模型关联的数据表
     *
     * @var string
     */
    protected $table = 'examines';

    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = ['id','created_at','updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function carInfo(){
        return $this->belongsTo(CarInfo::class);
    }
}