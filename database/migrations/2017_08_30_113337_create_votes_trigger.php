<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVotesTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
        CREATE TRIGGER count_votes_insert AFTER INSERT ON `votes` FOR EACH ROW
            BEGIN    
                CALL count_votes(NEW.event_id);
            END
        ');

        DB::unprepared('
        CREATE TRIGGER count_votes_update AFTER UPDATE ON `votes` FOR EACH ROW
            BEGIN    
                CALL count_votes(NEW.event_id);
            END
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER `count_votes_insert`');
        DB::unprepared('DROP TRIGGER `count_votes_update`');
    }
}
