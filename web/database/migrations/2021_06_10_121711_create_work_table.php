<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work', function (Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->index();
            $table->string('company', 100)->default('');
            $table->string('department', 100)->default('');
            $table->string('post', 50)->default('')->comment('user position at work');
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
        Schema::dropIfExists('work');
    }
}
