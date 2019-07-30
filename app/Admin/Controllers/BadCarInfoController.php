<?php
namespace App\Admin\Controllers;

use App\Admin\Exceptions\BadCarExcelExporter;
use App\Admin\Exceptions\CarExcelExporter;
use App\Admin\Model\CarInfo;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class BadCarInfoController extends Controller{
    use HasResourceActions;
    /**
     * @param Content $content
     * @return Content
     */
    public function index(Content $content){
        return $content->header("报废车辆")
                       ->description("列表")
                       ->body($this->grid());
    }
    
    public function grid(){
        $grid = new Grid(new CarInfo());
        $grid->model()->where('status','=',3);
        $grid->model()->orderBy('status');
        $grid->id('ID')->sortable();
        $grid->brand('品牌型号');
        $grid->code('车编号');
        $grid->carType('车型');
        $grid->license('车牌号');
        $grid->status('车辆状态')->display(function ($status){
            return "<span class='label label-danger'>报废的</span>";
        });
        $grid->inspection_t('年检时间')->display(function ($time){
            $t_arr = explode(' ',$time);
            
            return $t_arr[0];
        });
        $grid->created_at('创建时间');
        $grid->updated_at('更新时间');
        
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
            // 去掉编辑
            $actions->disableEdit();
        });
        
        $grid->tools(function (Grid\Tools $tools) {
            $tools->batch(function (Grid\Tools\BatchActions $actions) {
                $actions->disableDelete();
            });
        });
        $grid->filter(function($filter){
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            // 在这里添加字段过滤器
            $filter->like('brand', '品牌型号');
            $filter->like('license', '车牌号');
        });
        $grid->disableCreateButton();
        $grid->exporter(new BadCarExcelExporter());
        $grid->disableRowSelector();
        return $grid;
    }
    
    /**
     * @param $id
     *
     * @return bool
     */
    public function destroy($id)
    {
        $car = CarInfo::findOrFail($id);
        
        $car->delete();
        
        return $car;
    }
}