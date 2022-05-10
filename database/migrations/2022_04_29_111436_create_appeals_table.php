<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppealsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appeals', function (Blueprint $table) {
            $table->id();
            $table->string('fromId');
            $table->string('toId');
            $table->string('toName');
            $table->string('toName');
            $table->string('work_order_id');
            $table->string('work');
            $table->string('measure')->default('无');
            $table->string('content');
            $table->string('fromType');
            $table->string('toType');
            $table->string('status')->default(2);
            $table->json('img');
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
        Schema::dropIfExists('appeals');
    }
}
