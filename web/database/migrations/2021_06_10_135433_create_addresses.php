<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddresses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('country', 50)->default('');
            $table->string('provinces', 50)->default('');
            $table->string('city', 50)->default('');
            $table->string('address', 50)->default('');
            $table->string('address_type', 30)->default('another')->comment('the type of address to be grouped, such as home. if not specified, another is specified by default');
            $table->string('postcode', 10)->default('');
            $table->string('post_office_box_number', 10)->default('');
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
    public function down()
    {
        Schema::dropIfExists('addresses');
    }
}
