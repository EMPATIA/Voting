<?php

namespace App\Http\Controllers;

use App\Event;
use App\VoteMethods\Like;
use App\VoteMethods\MultiVote;
use Exception;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class TestControler extends Controller
{
    public function index(Request $request)
    {
        try{
            $event = Event::whereKey("GxTT0RlgcgYXZwf40hwUX7hDKDRYa5Nx")->firstOrFail();
            $test = new MultiVote($event);

            return response()->json(['data' => $test->getTotalSummary()], 200);
        }catch(Exception $e){
            dd($e);
        }
    }
}
