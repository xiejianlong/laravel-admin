<?php
namespace App\Admin\Service;
use App\Admin\Model\MessagesModel;
use Carbon\Carbon;

class MessageService{
    /**
     * @var MessagesModel
     */
    protected  $model;
    /**
     * @var string
     */
    protected  $now;

    /**
     * MessageService constructor.
     * @param MessagesModel $model
     */
    public function __construct(MessagesModel $model)
    {
        $this->model = $model;
        $this->now = Carbon::now()->toDateTimeString();
    }

    /**
     * @param int $from
     * @param string $to
     * @param string $title
     * @param string $message
     * @return bool
     */
    public function add(int $from,string $to,string $title,string $message){
        $this->model->from = $from;
        $this->model->to = $to;
        $this->model->title = $title;
        $this->model->message = $message;
        return $this->model->save();
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
        return $this->model->find($exa_id);
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
                $this->model->where($key,$v[0],$v[1]);
            }
            $this->model->orderBy($orderField,$order);
            return $this->model->get($field);
        }else{
            return false;
        }
    }
}