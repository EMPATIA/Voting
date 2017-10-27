<?php

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConfigurationTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('configuration_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('configuration_id');
            $table->string('language_code');
            $table->string('name');
            $table->text('description');

            $table->timestamps();
            $table->softDeletes();
        });
        $configurationTranslations = array(
            array('id' => '1',    'configuration_id' => '1',  'language_code' => 'en',  'name' => 'Allow Dislike',                    'description' => 'Allow negative voting',                           'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '2',    'configuration_id' => '2',  'language_code' => 'en',  'name' => 'Allow Multiple per One',           'description' => 'Allow more than one vote per Idea/Proposal',      'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '3',    'configuration_id' => '3',  'language_code' => 'en',  'name' => 'Total Votes allowed',              'description' => 'Total of votes per user',                         'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '4',    'configuration_id' => '4',  'language_code' => 'en',  'name' => 'Allow Multiple per One',           'description' => 'Allow more than one vote per Idea/Proposal',      'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '5',    'configuration_id' => '5',  'language_code' => 'en',  'name' => 'Total Votes allowed',              'description' => 'Total of positive and negative votes per user',   'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '6',    'configuration_id' => '6',  'language_code' => 'en',  'name' => 'Total Positive Votes allowed',     'description' => 'Total of positive votes per user',                'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '7',    'configuration_id' => '7',  'language_code' => 'en',  'name' => 'Total Negative Votes allowed',     'description' => 'Total of negative votes per user',                'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '8',    'configuration_id' => '1',  'language_code' => 'pt',  'name' => 'Allow Dislike',                    'description' => 'Allow negative voting',                           'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '9',    'configuration_id' => '2',  'language_code' => 'pt',  'name' => 'Allow Multiple per One',           'description' => 'Allow more than one vote per Idea/Proposal',      'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '10',   'configuration_id' => '3',  'language_code' => 'pt',  'name' => 'Total Votes allowed',              'description' => 'Total of votes per user',                         'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '11',   'configuration_id' => '4',  'language_code' => 'pt',  'name' => 'Allow Multiple per One',           'description' => 'Allow more than one vote per Idea/Proposal',      'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '12',   'configuration_id' => '5',  'language_code' => 'pt',  'name' => 'Total Votes allowed',              'description' => 'Total of positive and negative votes per user',   'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '13',   'configuration_id' => '6',  'language_code' => 'pt',  'name' => 'Total Positive Votes allowed',     'description' => 'Total of positive votes per user',                'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '14',   'configuration_id' => '7',  'language_code' => 'pt',  'name' => 'Total Negative Votes allowed',     'description' => 'Total of negative votes per user',                'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '15',   'configuration_id' => '9',  'language_code' => 'pt',  'name' => 'Ending value to rank',             'description' => 'Ending value to rank',                            'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '16',   'configuration_id' => '9',  'language_code' => 'cz',  'name' => 'Ending value to rank',             'description' => 'Ending value to rank',                            'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '17',   'configuration_id' => '9',  'language_code' => 'it',  'name' => 'Ending value to rank',             'description' => 'Ending value to rank',                            'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '18',   'configuration_id' => '9',  'language_code' => 'de',  'name' => 'Ending value to rank',             'description' => 'Ending value to rank',                            'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '19',   'configuration_id' => '9',  'language_code' => 'en',  'name' => 'Ending value to rank',             'description' => 'Ending value to rank',                            'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '20',   'configuration_id' => '9',  'language_code' => 'fr',  'name' => 'Ending value to rank',             'description' => 'Ending value to rank',                            'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '21',   'configuration_id' => '9',  'language_code' => 'es',  'name' => 'Ending value to rank',             'description' => 'Ending value to rank',                            'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '22',   'configuration_id' => '8',  'language_code' => 'pt',  'name' => 'Starting value to rank',           'description' => 'Starting value to rank',                          'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '23',   'configuration_id' => '8',  'language_code' => 'cz',  'name' => 'Starting value to rank',           'description' => 'Starting value to rank',                          'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '24',   'configuration_id' => '8',  'language_code' => 'it',  'name' => 'Starting value to rank',           'description' => 'Starting value to rank',                          'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '25',   'configuration_id' => '8',  'language_code' => 'de',  'name' => 'Starting value to rank',           'description' => 'Starting  value to rank',                         'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '26',   'configuration_id' => '8',  'language_code' => 'en',  'name' => 'Starting  value to rank',          'description' => 'Starting value to rank',                          'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '27',   'configuration_id' => '8',  'language_code' => 'fr',  'name' => 'Starting  value to rank',          'description' => 'Starting value to rank',                          'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '28',   'configuration_id' => '8',  'language_code' => 'es',  'name' => 'Starting value to rank',           'description' => 'Starting value to rank',                          'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL)
        );
        DB::table('configuration_translations')->insert($configurationTranslations);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('configuration_translations');
    }
}
