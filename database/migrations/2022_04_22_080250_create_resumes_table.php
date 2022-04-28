<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResumesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resumes', function (Blueprint $table) {
            $table->id();
            $table->string('openid')->index();
            $table->string('avatar');
            $table->string('name');
            $table->string('sex');
            $table->string('age');
            $table->string('phone');
            $table->string('education');

            $table->string('position');
            $table->string('salary');
            $table->string('city');

            $table->json('internship_experience');
            $table->json('project_experience');
            $table->string('self_assessment');
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
        Schema::dropIfExists('resumes');
    }
}
