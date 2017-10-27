<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMethodGroupTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('method_group_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('method_group_id');
            $table->string('language_code');
            $table->string('name');
            $table->text('description');
            $table->timestamps();
            $table->softDeletes();
        });

        $methodGroupTranslations = array(
            array(
                'method_group_id'   => 1,
                'language_code'       => 'en',
                'name'              => 'Web Platform',
                'description'       => 'Method for the web platform'
            ),
            array(
                'method_group_id'   => 2,
                'language_code'       => 'en',
                'name'              => 'CellPhones',
                'description'       => 'Method for cellPhones'
            )
        );
        DB::table('method_group_translations')->insert($methodGroupTranslations);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('method_group_translations');
    }
}
