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
            $table->uuid('id')->primary();
            $table->bigInteger('user_id');

            $table->string('first_name')->default('');
            $table->string('last_name')->default('');

            $table->string('middlename')->default('');
            $table->string('prefix')->default('');
            $table->string('suffix')->default('');
            $table->string('nickname')->default('');

            $table->string('adrpob')->default('');
            $table->string('adrextend')->default('');
            $table->string('adrstreet')->default('');
            $table->string('adrcity')->default('');
            $table->string('adrstate')->default('');
            $table->string('adrzip')->default('');
            $table->string('adrcountry')->default('');

            $table->boolean('is_favorite')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
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
