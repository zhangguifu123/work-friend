<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('openid')->unique();
            $table->string('name');
            $table->string('code');
            $table->string('industry');
            $table->string('legal_person');
            $table->string('phone');
            $table->string('avatar');
            $table->string('status');

            $table->string('address')->comment('公司地址');
            $table->string('company_size')->comment('公司规模');
            $table->string('registered_capital')->comment('注册资金');
            $table->string('incorporation')->comment('成立日期');
            $table->string('introduce')->comment('公司介绍');

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
        Schema::dropIfExists('companies');
    }
}
