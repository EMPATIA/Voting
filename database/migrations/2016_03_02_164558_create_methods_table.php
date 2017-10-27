<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMethodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('methods', function (Blueprint $table) {
            $table->increments('id');
            $table->string('method_group_id');
            $table->string('code');

            $table->timestamps();
            $table->softDeletes();
        });

        $methods = array(
            array(
                'method_group_id'   => 1,
                'code'              => 'like'
            ),
            array(
                'method_group_id'   => 1,
                'code'              => 'multi_voting'
            ),
            array(
                'method_group_id'   => 1,
                'code'              => 'negative_voting'
            ),
            array(
                'method_group_id'   => 1,
                'code'              => 'rank'
            )

        );
        DB::table('methods')->insert($methods);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('methods');
    }
}
