<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('emails', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('to_name')->nullable();
            $table->string('to_email');

            $table->string('from_name')->nullable();
            $table->string('from_email');

            $table->string('subject')->nullable();
            $table->longText('body')->nullable();

            $table->integer('postal_id');
            $table->string('postal_token');

            $table->timestamp('created_at')->nullable();

            $table->index(['postal_id', 'postal_token'], 'postal_id_token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('emails');
    }
}
