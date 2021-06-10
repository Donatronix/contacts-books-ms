<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContactEmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $table_name = 'contact_emails';
        Schema::create($table_name, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email');
            $table->boolean('is_default')->default(false);
            $table->foreignUuid('contact_id')->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->dateTime('created_at');
        });
        DB::statement("ALTER TABLE {$table_name} ADD `email_type` VARCHAR (30) DEFAULT NULL COMMENT 'the type of email field, for example: home, work, etc.'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_emails');
    }
}
