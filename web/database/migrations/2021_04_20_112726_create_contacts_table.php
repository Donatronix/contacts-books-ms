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
            $table->string('prefix_name', 20)->nullable()->comment('Prefix of contact name');
            $table->string('first_name', 100)->nullable();
            $table->string('middle_name', 50)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('suffix_name', 20)->nullable()->comment('Suffix of contact name');
            $table->string('write_as_name', 200)->nullable();
            $table->string('nickname', 50)->nullable()->comment('Nickname of contact');
            $table->date('birthday')->nullable();
            $table->text('note')->nullable();
            $table->boolean('is_favorite')->default(false);
            $table->uuid('user_id')->index();

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
