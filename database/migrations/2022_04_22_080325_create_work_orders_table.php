<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('openid')->index();
            $table->string('user_type');
            $table->string('order_type');
            $table->string('content');
            $table->string('place');
            $table->string('salary');
            $table->string('education');
            $table->string('dateline');
            $table->string('service_charge');
            $table->string('description');
            $table->integer("collection_count")->default(0)->comment("被收藏次数");

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
        Schema::dropIfExists('work_orders');
    }
}
