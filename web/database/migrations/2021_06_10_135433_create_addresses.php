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
            $table->string('country', 100)->nullable();
            $table->string('address_string1')->nullable();
            $table->string('address_string2')->nullable();
            $table->string('city', 50)->nullable();
            $table->string('provinces', 100)->nullable();
            $table->string('postcode', 10)->nullable();
            $table->string('po_box', 10)->nullable();
            $table->string('type', 30)->default('another')->comment('the type of address to be grouped, such as home. if not specified, another is specified by default');
            $table->boolean('is_default')->default(false);

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
