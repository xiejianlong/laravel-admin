<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->increments('id');
            $table->string('brand', 190)->comment("品牌型号");
            $table->string('code', 190)->unique()->comment("车编号");
            $table->string('carType', 64)->comment("车型");
            $table->string('license', 190)->unique()->comment("车牌号");
            $table->tinyInteger('status')->default(0)->comment("车辆状态 ");
            $table->dateTime('inspection_t')->comment("年检时间");
            $table->string('c_name', 50)->comment("创建人");
            $table->string('e_name', 50)->nullable()->comment("编辑人");
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
        Schema::dropIfExists('cars');
    }
}
