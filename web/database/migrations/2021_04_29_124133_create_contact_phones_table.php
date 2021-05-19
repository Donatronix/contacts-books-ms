<?php

use App\Models\ContactPhone;
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
    public function up()
    {
        Schema::create('contact_phones', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('contact_id')->references('id')->on('contacts')->onUpdate('cascade')->onDelete('cascade');

            $table->string('phone');

            $table->enum('type', ['cell', 'work', 'home', 'other'])->default(ContactPhone::TYPE_CELL);

            $table->boolean('is_default')->default(false);

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
        Schema::dropIfExists('contact_phones');
    }
}
