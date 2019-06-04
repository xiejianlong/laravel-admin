<?php
namespace App\Admin\Service;

use App\Admin\Model\CarInfo;

class CarInfoService {
    protected $CarModel;
    public function __construct(CarInfo $carInfo)
    {
        $this->CarModel = $carInfo;
    }
    /**
     * @param array $add
     * @return bool
     */
    public function add(array $add){
        if(!empty($add)){
            foreach ($add as $k=>$v){
                $this->CarModel->$k=$v;
            }
            $res = $this->CarModel->save();
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
        return $this->CarModel->find($id);
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
                $this->CarModel->where($key,$v[0],$v[1]);
            }
            $this->CarModel->orderBy($orderField,$order);
            return $this->CarModel->get($field);
        }else{
            return false;
        }
    }
}