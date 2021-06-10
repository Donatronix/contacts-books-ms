<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('works', function (Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->index();
            $table->string('company', 100)->default('');
            $table->string('department', 100)->default('');
            $table->string('post', 50)->default('')->comment('user position at work');
            $table->foreignUuid('contact_id')->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
}
