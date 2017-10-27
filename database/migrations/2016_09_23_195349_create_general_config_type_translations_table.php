<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGeneralConfigTypeTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('general_config_type_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('general_config_type_id');
            $table->string('language_code');
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        $generalConfigTypeTranslations = array(
            array(
                'general_config_type_id'   => 1,
                'language_code'       => 'en',
                'name'              => 'Minimum Age'
            ),
            array(
                'general_config_type_id'   => 1,
                'language_code'       => 'pt',
                'name'              => 'Idade Mínima'
            ),array(
                'general_config_type_id'   => 2,
                'language_code'       => 'en',
                'name'              => 'Age'
            ),
            array(
                'general_config_type_id'   => 2,
                'language_code'       => 'pt',
                'name'              => 'Idade'
            )
        );
        DB::table('general_config_type_translations')->insert($generalConfigTypeTranslations);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('general_config_type_translation');
    }
}
