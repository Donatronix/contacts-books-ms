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

            $table->boolean('is_favorite')->default(false);
            $table->bigInteger('user_id')->index();
            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement("ALTER TABLE {$table_name} ADD `avatar` TEXT  DEFAULT NULL");
        DB::statement("ALTER TABLE {$table_name} ADD `nickname` VARCHAR(50) DEFAULT NULL COMMENT 'user alias'");
        DB::statement("ALTER TABLE {$table_name} ADD `user_prefix` VARCHAR(20) DEFAULT NULL COMMENT 'prefix user name'");
        DB::statement("ALTER TABLE {$table_name} ADD `user_suffix` VARCHAR (20) DEFAULT NULL COMMENT 'suffix user name'");
        DB::statement("ALTER TABLE {$table_name} ADD `birthday` DATE DEFAULT NULL");
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
