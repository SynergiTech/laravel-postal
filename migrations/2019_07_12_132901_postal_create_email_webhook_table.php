<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PostalCreateEmailWebhookTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $model = config('postal.models.webhook');
        $table = (new $model())->getTable();

        if (Schema::hasTable($table)) {
            return;
        }

        Schema::create($table, function (Blueprint $table) {
            $emailModel = config('postal.models.email');
            $emailTable = (new $emailModel())->getTable();

            $table->bigIncrements('id');

            $table->unsignedBigInteger('email_id');
            $table->foreign('email_id')->references('id')->on($emailTable)->onDelete('cascade');

            $table->string('action')->nullable();

            $table->longText('payload')->nullable();

            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $model = config('postal.models.webhook');
        $table = (new $model())->getTable();

        Schema::dropIfExists($table);
    }
}
