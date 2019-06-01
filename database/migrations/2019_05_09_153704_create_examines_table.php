<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExaminesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('examines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('car_id')->commemt("carID");
            $table->string('brand', 190)->comment("品牌型号");
            $table->string('code', 190)->comment("车编号");
            $table->string('carType', 64)->comment("车型");
            $table->string('license', 190)->comment("车牌号");
            $table->tinyInteger('status')->comment("车辆状态 ");
            $table->text('msg')->comment("申请备注");
            $table->string('name', 50)->comment("申请人");
            $table->string('e_name', 50)->nullable()->comment("审批人");
            $table->dateTime('e_time')->nullable()->comment("审批时间");
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
        Schema::dropIfExists('examines');
    }
}
