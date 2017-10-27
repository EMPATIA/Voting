<?php

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateConfigurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('configurations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('method_id');
            $table->string('code');
            $table->string('parameter_type');
            
            $table->timestamps();

            $table->softDeletes();
        });

        $configurations = array(
            array('id' => '1',	'method_id' => '1',	'code' => 'allow_dislike',					'parameter_type' => 'boolean',	'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '2',	'method_id' => '2',	'code' => 'allow_multiple_per_one',			'parameter_type' => 'boolean',	'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '3',	'method_id' => '2',	'code' => 'total_votes_allowed',			'parameter_type' => 'numeric',	'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '4',	'method_id' => '3',	'code' => 'allow_multiple_per_one',			'parameter_type' => 'boolean',	'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '5',	'method_id' => '3',	'code' => 'total_votes_allowed',			'parameter_type' => 'numeric',	'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '6',	'method_id' => '3',	'code' => 'total_positive_votes_allowed',	'parameter_type' => 'numeric',	'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '7',	'method_id' => '3',	'code' => 'total_negative_votes_allowed',	'parameter_type' => 'numeric',	'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '8',	'method_id' => '4',	'code' => 'rank_range_start',				'parameter_type' => 'numeric',	'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL),
            array('id' => '9',	'method_id' => '4',	'code' => 'rank_range_end',					'parameter_type' => 'numeric',	'created_at' => Carbon::now(),'updated_at' => Carbon::now(),'deleted_at' => NULL)
        );
        DB::table('configurations')->insert($configurations);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('configurations');
    }
}
