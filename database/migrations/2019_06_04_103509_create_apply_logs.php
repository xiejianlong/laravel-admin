<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApplyLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('apply_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('exa_id')->commemt("examines");
            $table->string('status',64)->comment("状态 ");
            $table->text('msg')->comment("申请备注/审批意见");
            $table->string('e_name', 50)->nullable()->comment("操作人");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('apply_logs');
    }
}
