<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMethodTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('method_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('method_id');
            $table->string('language_code');
            $table->string('name');
            $table->text('description');

            $table->timestamps();
            $table->softDeletes();
        });

        $methodTranslations = array(
            array(
                'method_id'     => 1,
                'language_code' => 'en',
                'name'          => 'Likes',
                'description'   => 'Method to use Likes'
            ),
            array(
                'method_id'     => 2,
                'language_code' => 'en',
                'name'          => 'Multi Voting',
                'description'   => 'Method for Multi Voting, use a number max of votes'
            ),
            array(
                'method_id'     => 3,
                'language_code'   => 'en',
                'name'          => 'Negative Voting',
                'description'   => 'Method for Negative Voting, can vote negative and positive'
            ), array(
                'method_id'     => 4,
                'language_code' => 'en',
                'name'          => 'Rank Voting',
                'description'   => 'Method for Rank Voting, can vote by giving a rank'
            )
        );
        DB::table('method_translations')->insert($methodTranslations);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('method_translations');
    }
}
