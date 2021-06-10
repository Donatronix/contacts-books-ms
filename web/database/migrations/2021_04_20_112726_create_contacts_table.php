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
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('first_name', 50)->default('');
            $table->string('last_name', 50)->default('');
            $table->string('surname', 50)->default('');
            $table->date('birthday')->default('');
            $table->text('avatar')->default('');
            $table->string('nickname', 50)->default('')->comment('user alias');
            $table->string('prefix', 50)->default('')->comment('prefix user name');
            $table->string('suffix', 50)->default('')->comment('suffix user name');
            $table->string('suffix', 50)->default('')->comment('suffix user name');


//            $table->string('middlename')->default('');
//            $table->string('prefix')->default('');
//            $table->string('suffix')->default('');
//            $table->string('adrpob')->default('');
//            $table->string('adrextend')->default('');
//            $table->string('adrstreet')->default('');
//            $table->string('adrcity')->default('');
//            $table->string('adrstate')->default('');
//            $table->string('adrzip')->default('');
//            $table->string('adrcountry')->default('');

            $table->boolean('is_favorite')->default(false);
            $table->bigInteger('user_id')->index();
            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
}
