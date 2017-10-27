<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGeneralConfigTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('general_config_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('general_config_type_key')->unique();
            $table->string('code')->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        $generalConfigs = array(
            array(
                'general_config_type_key'   => 'lkdfjkbhJfmmtdcKKGvbvajsT',
                'code'                      => 'minimum_age'
            ),
            array(
                'general_config_type_key'   => 'lkdfjkbhJfmmtdcKKGvbvajsd',
                'code'                      => 'age'
            ),

        );
        DB::table('general_config_types')->insert($generalConfigs);
    }

    /**
     * Reverse the migrations. n
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('general_config_types');
    }
}
