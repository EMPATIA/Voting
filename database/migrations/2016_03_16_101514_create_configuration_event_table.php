<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConfigurationEventTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('configuration_events', function (Blueprint $table) {
            $table->increments('id');
            $table->string('configuration_event_key');
            $table->string('configuration_id');
            $table->string('event_id');
            $table->string('general_config_id')->nullable();
            $table->string('value');
            $table->string('created_by');
            $table->timestamps();

            $table->softDeletes();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('configuration_events');
    }
}
