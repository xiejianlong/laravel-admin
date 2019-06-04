<?php
namespace App\Admin\Service;

use App\Admin\Model\CarExamine;

class CarExamineService {
    /**
     * @var CarExamine
     */
    protected $carExamine;

    /**
     * CarExamineService constructor.
     * @param CarExamine $carExamine
     */
    public function __construct(CarExamine $carExamine)
    {
        $this->carExamine = $carExamine;
    }

    /**
     * @param array $add
     * @return bool
     */
    public function add(array $add){
        if(!empty($add)){
            foreach ($add as $k=>$v){
                $this->carExamine->$k=$v;
            }
            $res = $this->carExamine->save();
        }else{
            $res = false;
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
        return $model->update();
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function find(int $id){
        return $this->carExamine->find($id);
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
                $this->carExamine->where($key,$v[0],$v[1]);
            }
            $this->carExamine->orderBy($orderField,$order);
            return $this->carExamine->get($field);
        }else{
            return false;
        }
    }
}