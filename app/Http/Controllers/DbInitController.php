<?php

namespace App\Http\Controllers;

use App\Event;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DbInitController extends Controller
{
    public function manualUpdateVotesCount(){
        try {
            $events = Event::withTrashed()->get();

            foreach ($events as $event){
                \DB::unprepared('CALL count_votes('.$event->id.');');
            }
        }catch(Exception $e){
            dd($e->getMessage(), $e->getLine());
        }
    }
}
