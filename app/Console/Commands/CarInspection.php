<?php

namespace App\Console\Commands;

use App\Admin\Service\CarInfoService;
use App\Admin\Service\MessageService;
use Encore\Admin\Facades\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CarInspection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:CarInspection';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '车辆年检定期提醒';


    protected $carInfoService;
    protected  $messageService;
    /**
     * CarInspection constructor.
     * @param MessageService $messageService
     * @param CarInfoService $carInfoService
     */
    public function __construct(MessageService $messageService,CarInfoService $carInfoService)
    {
        parent::__construct();
        $this->messageService = $messageService;
        $this->carInfoService = $carInfoService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $now = Carbon::today()->addYear(1)->subDay(10)->toDateTimeString();
        $where['inspection_t']=['>=',$now];
        $car = $this->carInfoService->select($where,['*']);
        if($car){
            foreach ($car as $k=>$v){
                $msg = '车辆：'.$v->license.'将在十天后达到年检最后期限，请管理员注意';
                $this->messageService->add(1,0,'车辆年检到期时间提醒',$msg);
            }
        }
    }
}
