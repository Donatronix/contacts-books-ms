<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContactPhonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $table_name = 'contact_phones';
        Schema::create($table_name, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('phone');
            $table->boolean('is_default')->default(false);
            $table->foreignUuid('contact_id')->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->dateTime('created_at');
        });

        DB::statement("ALTER TABLE {$table_name} ADD `phone_type` VARCHAR (30) DEFAULT NULL COMMENT 'a field type for a phone that denotes a group, such as home'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_phones');
    }
}
