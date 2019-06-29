<?php
namespace App\Admin\Model;

use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Model;
class MessagesModel extends Model
{
    /**
     * 与模型关联的数据表
     *
     * @var string
     */
    protected $table = 'admin_messages';

    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = ['id','created_at','updated_at'];

    public function sender()
    {
        return $this->belongsTo(Administrator::class, 'from');
    }
    public function receiver()
    {
        return $this->belongsTo(Administrator::class, 'to');
    }
    
    public function inbox(){
        $this->where('to', Admin::user()->id);
        return $this;
    }
    public function outbox(){
        $this->where('from', Admin::user()->id);
        return $this;
    }
    public function unread(){
        $this->whereNull('read_at');
        return $this;
    }
    public function scopeInbox($query)
    {
        return $query->where('to', Admin::user()->id);
    }
    public function scopeOutbox($query)
    {
        return $query->where('from', Admin::user()->id);
    }
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }
    /*public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->from = Admin::user()->id;
            if (is_array($model->to)) {
                foreach ($model->to as $to) {
                    $new = clone $model;
                    $new->to = $to;
                    $new->save();
                }
                return false;
            }
        });
    }*/
}