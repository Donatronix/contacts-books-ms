<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->uuid('id');

            $table->bigInteger('commonid')->default(0);

            //$table->bigInteger('user_id')->default(0);


            $table->string('firstname')->default('');
            $table->string('lastname')->default('');
            $table->string('middlename')->default('');
            $table->string('prefix')->default('');
            $table->string('suffix')->default('');
            $table->string('nickname')->default('');

            $table->string('tel1')->default('');
            $table->string('tel2')->default('');
            $table->string('email')->default('');

            $table->string('adrpob')->default('');
            $table->string('adrextend')->default('');
            $table->string('adrstreet')->default('');
            $table->string('adrcity')->default('');
            $table->string('adrstate')->default('');
            $table->string('adrzip')->default('');
            $table->string('adrcountry')->default('');

            $table->timestamps();

            $table->index('user_id');
            $table->index(['tel1', 'commonid']);
            $table->index(['tel2', 'commonid']);
            $table->index('commonid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contacts');
    }
}
