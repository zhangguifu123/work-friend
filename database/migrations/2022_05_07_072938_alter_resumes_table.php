<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterResumesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('resumes', function (Blueprint $table) {
            $table->string('worker_id');
        });
        Schema::table('work_orders', function (Blueprint $table) {
            $table->string('status')->default("2");
        });
        Schema::table('application_orders', function (Blueprint $table) {
            $table->string('publisher_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
