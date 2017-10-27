<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGeneralConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('general_configs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('general_config_key')->unique();
            $table->integer('general_config_type_id');
            $table->string('parameter_key');
            $table->boolean('greater');
            $table->boolean('equal');
            $table->boolean('less');
            $table->string('value');
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
        Schema::drop('general_configs');
    }
}
