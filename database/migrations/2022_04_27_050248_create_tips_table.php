<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tips', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->comment('反馈人');
            $table->string('user_name', 20)->comment('反馈人姓名');
            $table->string('title', 20)->comment('反馈理由');
            $table->string('content')->comment('反馈描述')->default(0);
            $table->boolean('status')->default(0)->comment('是否审核');
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
        Schema::dropIfExists('tips');
    }
}
