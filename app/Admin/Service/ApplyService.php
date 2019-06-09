<?php
namespace App\Admin\Service;

use App\Admin\Model\ApplyLogs;
use Illuminate\Support\Facades\Notification;

class ApplyService {
    /**
     * @var ApplyLogs
     */
    protected  $logs;
    protected  $notification;

    /**
     * ApplyService constructor.
     * @param ApplyLogs $logs
     */
    public function __construct(ApplyLogs $logs,Notification $notification)
    {
        $this->logs = $logs;
        $this->notification = $notification;
    }

    /**
     * @param int $exa_id
     * @param string $status
     * @param string $msg
     * @param string $e_name
     * @return bool
     */
    public function add(int $exa_id,string $status,string $msg,string $e_name){
        $this->logs->exa_id = $exa_id;
        $this->logs->status = $status;
        $this->logs->msg = $msg;
        $this->logs->e_name = $e_name;
        $res= $this->logs->save();
        if($res){
            $this->notification::send();
        }
        return $res;
    }

    /**
     * @param int $id
     * @param array $update
     * @return mixed
     */
    public function update(int $id,array $update){
        $model = $this->find($id);
        if(!empty($update)&&$model){
            foreach ($update as $k=>$v){
                $model->$k = $v;
            }
        }
        return $model->save();
    }

    /**
     * @param int $exa_id
     * @return mixed
     */
    public function find(int $exa_id){
        return $this->logs->find($exa_id);
    }

    /**
     * @param array $where
     * @param array $field
     * @param string $orderField
     * @param string $order
     * @return bool
     */
    public function select(array $where,array $field,string $orderField='created_at',string $order='asc'){

        if(!empty($where)){
            foreach ($where as $key=>$v){
                $this->logs->where($key,$v[0],$v[1]);
            }
            $this->logs->orderBy($orderField,$order);
            return $this->logs->get($field);
        }else{
            return false;
        }
    }
}