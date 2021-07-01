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
        $table_name = "contacts";
        Schema::create($table_name, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('first_name', 50)->default('');
            $table->string('last_name', 50)->default('');
            $table->string('surname', 50)->default('');
            $table->boolean('avatar')->default(0);
            $table->date('birthday')->nullable();
            $table->string('nickname', 50)->nullable()->comment('user alias');
            $table->string('user_prefix', 20)->nullable()->comment('prefix user name');
            $table->string('user_suffix', 20)->nullable()->comment('suffix user name');


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
