<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PostalCreateEmailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $model = config('postal.models.email');
        $table = (new $model())->getTable();

        if (Schema::hasTable($table)) {
            return;
        }

        Schema::create($table, function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('to_name')->nullable();
            $table->string('to_email');

            $table->string('from_name')->nullable();
            $table->string('from_email');

            $table->string('subject')->nullable();
            $table->longText('body')->nullable();

            $table->string('postal_email_id');
            $table->integer('postal_id');
            $table->string('postal_token');

            // must be nullable as morph is optional and
            // if selected, added later
            $table->nullableMorphs('emailable');

            $table->timestamp('created_at')->nullable();

            // index for searching groups of emails
            $table->index('postal_email_id');

            // index for webhook searching
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
        $model = config('postal.models.email');
        $table = (new $model())->getTable();

        Schema::dropIfExists($table);
    }
}
