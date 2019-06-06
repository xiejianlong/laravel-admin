<?php
namespace App\Admin\Controllers;

use App\Admin\Service\MessageService;
use App\Http\Controllers\Controller;
use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\Redis;

class MessageController extends Controller{
    private $messageService;
    public function __construct(MessageService $messageService)
    {
        $this->messageService->$messageService;
        Admin::user();
    }

}