<?php

namespace App\Http\Controllers;

use App\Event;
use App\EventLevel;
use App\GeneralConfig;
use App\Method;
use App\One\One;
use App\UserEventCode;
use App\UserEventCodeVote;
use App\VoteMethods\Like;
use App\VoteMethods\MultiVote;
use App\VoteMethods\NegativeVote;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use Psy\Exception\Exception;

/**
 * Class EventsController
 * @package App\Http\Controllers
 */

class EventLevelsController extends Controller
{
    /**
     * Request list of all EventLevels
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $eventLevels = EventLevel::all();

            return response()->json(['data' => $eventLevels], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to retrieve the EventLevels list'], 500);
        }
    }

    /**
     * Request of one EventLevel
     * Returns the attributes of the EventLevel
     * @param Request $request
     * @param $id
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
     */
    public function show(Request $request, $id)
    {
        try {
            $eventLevel = EventLevel::whereId($id)->firstOrFail();

            $events = $eventLevel->events()->get();

            return response()->json($events, 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'EventLevels not Found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to retrieve the EventLevels'], 500);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * Store a new EventLevel in the database
     * Return the Attributes of the EventLevel
     * @param Request $request
     * @return static
     */
    public function store(Request $request)
    {
        try {
            foreach ($request->values as $key => $value) {
                $voteId=Event::where('key','=', $key)->first();
                $event = EventLevel::create(
                    [
                        'cb_key'       => $request->cb_key,
                        'event_id'     => $voteId->id,
                        'value'        => json_encode($value)
                    ]
                );
            }
            return response()->json('OkStoreEventLevel', 200);

        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to store the EventLevel'], 500);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * Update a existing Event
     * Return the Attributes of the Event Updated
     * @param Request $request
     * @return mixed
     */
    public function update(Request $request)
    {
        try {
            if(! (is_null($request->values))){
                $eventLevel =EventLevel::where('cb_key','=',$request->cb_key);
                $eventLevel->delete();
                foreach ($request->values as $key => $value) {
                    $voteId=Event::where('key','=',  $key)->first();

                    $eventLevel = EventLevel::create(
                        [

                            'cb_key'       => $request->cb_key,
                            'event_id'     => $voteId->id,
                            'value'        => json_encode($value)
                        ]
                    );
                }
                return response()->json('OkUpdateEventLevel', 200);
            }else{
                $eventLevel =EventLevel::where('cb_key','=',$request->cb_key)->get();
                foreach ($eventLevel as  $value) {
                    $value->delete();
                }
                return response()->json('OkUpdateEventLevel', 200);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'EventLevel not Found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to update EventLevel'], 500);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function eventLevel(Request $request)
    {
        try {
            $voteId = Event::where('key','=', $request->event_key)->first();
            $eventLevel =EventLevel::where('cb_key','=',$request->cb_key)->where('event_id','=',$voteId->id)->get();

            return response()->json($eventLevel, 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'EventLevel not Found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to retrieve the EventLevel'], 500);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function eventLevelCbKey(Request $request)
    {
        try {
            $eventId = [];
            $eventLevel = EventLevel::where('cb_key','=',$request->cb_key)->get();
            foreach ($eventLevel as $key => $value) {
                $vote = Event::where('id','=',$value['event_id'])->first();
                $eventId[$vote->key]=$value['value'];
            }
            return response()->json($eventId, 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'EventLevel not Found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to retrieve the EventLevelCbKey'], 500);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }
}
