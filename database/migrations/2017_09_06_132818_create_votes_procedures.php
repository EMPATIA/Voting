<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVotesProcedures extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
        CREATE PROCEDURE count_votes( IN new_event_id INT(10) ) 
            BEGIN
                DECLARE var_vote_key VARCHAR(64);
                DECLARE var_total INT DEFAULT 0;
                DECLARE var_positive INT;
                DECLARE var_negative INT;
                DECLARE var_neutral INT;
                DECLARE var_sum_positive INT;
                DECLARE var_sum_negative INT;
                DECLARE var_total_users INT;
                DECLARE done INT DEFAULT false;
                
                DECLARE cur CURSOR FOR
                    SELECT `vote_key`, 
                    COUNT(IF(value>0,1,NULL)) AS positive, 
                    COUNT(IF(value<0,1,NULL)) AS negative,
                    COUNT(IF(value=0,1,NULL)) AS neutral,
                    SUM(IF(value>0,value,NULL)) AS sum_positive,
                    SUM(IF(value<0,value,NULL)) AS sum_negative
                    FROM votes
                    WHERE 
                    `deleted_at` IS NULL AND
                    `event_id`=new_event_id
                    GROUP BY `vote_key`;
                    
                DECLARE continue handler for not found SET done = true; 
                
                SET @json = \'{"topics": {\';
                open cur;
                    my_loop: loop
                    
                    set done = false;
                    
                    fetch cur into var_vote_key, var_positive, var_negative, var_neutral, var_sum_positive, var_sum_negative;
                    
                    if done then
                        leave my_loop;
                    end if;
                    
                    SELECT JSON_OBJECT(
                        \'positive\', var_positive,
                        \'negative\', var_negative,
                        \'neutral\', var_neutral,
                        \'sum_positive\', var_sum_positive,
                        \'sum_negative\', var_sum_negative
                    )
                    INTO @response;
                    
                    SET @json = CONCAT(@json,\'"\',var_vote_key,\'":\' ,@response, \',\');

                    SET var_total = var_total + var_positive + var_negative;

                    end loop my_loop;
                
                close cur;
                
                SET @json = TRIM(TRAILING \',\' FROM @json);

                SET var_total_users=(
                SELECT COUNT(DISTINCT `user_key`)
                FROM `votes`
                WHERE 
                `deleted_at` IS NULL AND
                `event_id`=new_event_id
                );

                SET @json = CONCAT(@json, \'},"count":{"total":\',var_total,\',"total_users":\',var_total_users,\'}}\');
                
                UPDATE events 
                SET _count_votes = @json 
                WHERE id = new_event_id;
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
        DB::unprepared('DROP PROCEDURE IF EXISTS count_votes');
    }
}
