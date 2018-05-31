<?php

namespace App\Http\Controllers;

use App\ComModules\Empatia;
use App\Event;
use App\Jobs\SaveVotesInTopic;
use App\One\One;
use App\Vote;
use App\VoteMethods\MultiVote;
use App\VoteMethods\NegativeVote;
use App\VoteMethods\Like;
use App\VoteMethods\PositiveVote;
use App\VoteMethods\RankVote;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

use App\Http\Requests;

/**
 * Class VotesController
 * @package App\Http\Controllers
 */
class VotesController extends Controller
{
    protected $keysRequired = [
        'event_key',
        'vote_key',
        'value'
    ];

    private $configs = [
        "likes" => [
            'dislike' => 'AllowDislike'
        ],
        "positiveVotes" => [
            'multi' => 'AllowMulti',
            'total' => 'TotalVotes'
        ],
        "negativeVotes" => [
            'multi' => 'AllowMulti',
            'total' => 'TotalVotes',
            'positive' => 'MaxPositive',
            'negative' => 'MaxNegative'
        ],
    ];

    /**
     * @SWG\Tag(
     *   name="Vote",
     *   description="Everything about Votes",
     * )
     * /


    /**
     * @param $event
     * @return bool
     * @throws Exception
     */
    private function checkOpenVoting($event)
    {
        $currentDate = Carbon::now();
        $currentDay = Carbon::today();

        $eventStartDate = new Carbon($event->start_date);
        $eventEndDate = new Carbon($event->end_date);

        $eventStartTime = new Carbon($event->start_time);
        $eventEndTime = new Carbon($event->end_time);

        if(($eventStartDate->gt($currentDate) && $eventStartTime->gt($currentDate)) || ($currentDay->gt($eventEndDate)) || ( $currentDay->eq($eventEndDate) && $currentDate->gt($eventEndTime)) ){
            throw new Exception('Voting is closed');
        }

        return true;
    }

    /**
     * Return the Attribute if can vote
     * @param Request $request
     * @return static
     */
    public function show(Request $request, $eventKey)
    {
        $userKey = ONE::verifyToken($request);

        if(ONE::verifyRole($userKey,$request) == 'manager' || ONE::verifyRole($userKey,$request) == 'admin'){
            if(!empty($request->user_key)){
                $userKey = $request->user_key;
            }
        }
        try {
            $event = Event::where("key",$eventKey)->firstOrFail();

            switch ($event->method_id) {
                case 1:
                    $like = new Like($event);
                    $canVotePositive = $like->canVote($userKey, $request->vote_key, 1);
                    $canVoteNegative = $like->canVote($userKey, $request->vote_key, -1);
                    $votesRemaining = null;
                    $voteValue = $like->getValueVote($userKey, $request->vote_key);
                    break;
                case 2:
                    $positiveVote = new MultiVote($event);
                    $canVotePositive = $positiveVote->canVote($userKey, $request->vote_key, 1);
                    $canVoteNegative = $positiveVote->canVote($userKey, $request->vote_key, -1);
                    $votesRemaining = $positiveVote->getRemainingVotes($userKey);
                    $voteValue = $positiveVote->getValueVote($userKey, $request->vote_key);
                    break;
                case 3:
                    $negativeVote = new NegativeVote($event);
                    $canVotePositive = $negativeVote->canVote($userKey, $request->vote_key, 1);
                    $canVoteNegative = $negativeVote->canVote($userKey, $request->vote_key, -1);
                    $votesRemaining = $negativeVote->getRemainingVotes($userKey);
                    $voteValue = $negativeVote->getValueVote($userKey, $request->vote_key);
                    break;
            }
            if($votesRemaining['total']==0){$votesRemaining['negative']=0;}

            $response = [
                "vote" => $voteValue,
                "positive" => $canVotePositive,
                "negative" => $canVoteNegative,
                "remainingVotes" => $votesRemaining
            ];

            return response()->json($response, 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to show the Vote'], 500);
        }
    }

    /**
     * Return the Attribute if can vote
     * @param Request $request
     * @return static
     */
    public function showAll(Request $request, $eventKey)
    {
        $userKey = ONE::verifyToken($request);

        if(ONE::verifyRole($userKey,$request) == 'manager' || ONE::verifyRole($userKey,$request) == 'admin'){
            if(!empty($request->user_key)){
                $userKey = $request->user_key;
            }
        }

        try {
            $event = Event::where("key",$eventKey)->firstOrFail();
            $votes = $event->votes()->whereIn('vote_key', $request->json('vote_keys'))->get();

            foreach ($votes as $vote) {
                switch ($event->method_id) {
                    case 1:
                        $like = new Like($event);
                        $canVotePositive = $like->canVote($userKey, $vote->vote_key, 1);
                        $canVoteNegative = $like->canVote($userKey, $vote->vote_key, -1);
                        $votesRemaining = null;
                        $voteValue = $like->getValueVote($userKey, $vote->vote_key);
                        break;
                    case 2:
                        $positiveVote = new MultiVote($event);
                        $canVotePositive = $positiveVote->canVote($userKey, $vote->vote_key, 1);
                        $canVoteNegative = $positiveVote->canVote($userKey, $vote->vote_key, -1);
                        $votesRemaining = null;
                        $voteValue = $positiveVote->getValueVote($userKey, $vote->vote_key);
                        break;
                    case 3:
                        $negativeVote = new NegativeVote($event);
                        $canVotePositive = $negativeVote->canVote($userKey, $vote->vote_key, 1);
                        $canVoteNegative = $negativeVote->canVote($userKey, $vote->vote_key, -1);
                        $votesRemaining = $negativeVote->getRemainingVotes($userKey);
                        $voteValue = $negativeVote->getValueVote($userKey, $vote->vote_key);
                        break;
                }

                if($votesRemaining['total']==0){$votesRemaining['negative']=0;}

                $response[] =
                    ([$vote->vote_key => [
                        "vote" => $voteValue,
                        "positive" => $canVotePositive,
                        "negative" => $canVoteNegative,
                        "remainingVotes" => $votesRemaining
                    ]]);
            }
            return response()->json($response, 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to show the Votes'], 500);
        }
    }

    // Note: This method has a copy in the smsVote method. Any change made here should be replicated to that function.
    /**
     * Store a new Vote in the database
     * Return the Attributes of the Vote created
     * @param Request $request
     * @return static
     */
    public function store(Request $request)
    {
        $userKey = ONE::verifyToken($request);

        $file = fopen(base_path("/app/Http/Controllers/VotesController.php"),'r');

        flock($file,LOCK_EX);
        \Log::info("VOTE [U:".$userKey."] Locked File");

        ONE::verifyKeysRequest($this->keysRequired, $request);
        $message = "";
        $value = "";
        $source = !empty($request->json('source')) ? $request->json('source') : "";
        /*
         * Verify if the value of the vote is -1 (negative),  1 (positive)
         */
        $role = ONE::verifyRole($userKey,$request);
        if($role == 'manager' || $role == 'admin'){
            if(!empty($request->json('user_key'))){
                $userKey = $request->json('user_key');
                $source = 'in_person';
            }
        }
        //THIS MEANS THAT THE USER IS LOGGED IN AND IS NOT IN PERSON VOTING
        /*  if(empty($request->json('user_key'))) {
              if (!Empatia::checkIfUserHasAllLoginLevelsToVote($request->json('event_key'), $userKey, $request->header('X-ENTITY-KEY'), $request->header('LANG-CODE'))) {
                  return response()->json(['error' => 'Unauthorized'], 401);
              }
          }*/
        try {
            $eventKey = $request->json('event_key');
            $event = Event::where("key",$eventKey)->firstOrFail();
            if (empty($request->type_id) && ((($event->method_id == 1 || $event->method_id == 2 || $event->method_id == 3) && ($request->json('value') == -1 || $request->json('value') == 1)) || $event->method_id == 4 )) {
                $canVote = false;
                $summary = null;

                // Verify date event!
                $this->checkOpenVoting($event);

                $requestValue = $request->json('value');
                $requestVoteKey = $request->json('vote_key');

                switch ($event->method_id) {
                    case 1:
                        $like = new Like($event);
                        $canVote = $like->canVote($userKey, $requestVoteKey,$requestValue);

                        if ($requestValue == 1 && $event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->where('value', '<', '0')->exists()) {
                            $event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->where('value', '<', '0')->first()->delete();
                        } elseif ($requestValue == 1 && $event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->where('value', '>', '0')->exists()) {
                            $event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->where('value', '>', '0')->first()->delete();
                            $value = 0;
                        } elseif ($requestValue == -1 && $event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->where('value', '>', '0')->exists() && $like->getConfigurationValues($this->configs['likes']['dislike']) == 1) {
                            $event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->where('value', '>', '0')->first()->delete();
                        } elseif ($requestValue == -1 && $event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->where('value', '<', '0')->exists() && $like->getConfigurationValues($this->configs['likes']['dislike']) == 1) {
                            $event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->where('value', '<', '0')->first()->delete();
                            $value = 0;
                        }
                        $message = $like->getMessage();
                        break;
                    case 2:
                        $positiveVote = new MultiVote($event);
                        $verifyVoteSubmit = $positiveVote->verifyVoteSubmit($userKey);
                        $voteExists = $event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->where('value', '>', '0')->exists();

                        if(!empty($request->type_id)){
                            $canVote = true;
                        }else{
                            if (
                                $requestValue == 1
                                && $voteExists
                                && $verifyVoteSubmit
                                && $positiveVote->getConfigurationValues($this->configs['positiveVotes']['multi'], $userKey) == 0
                            ) {
                                $event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->where('value', '>', '0')->first()->delete();
                                $value = 0;
                            } elseif (
                                $requestValue == 1
                                && $voteExists
                                && $positiveVote->getConfigurationValues($this->configs['positiveVotes']['multi'], $userKey) == 1
                            ) {
                                $canVote = $positiveVote->canVote($userKey, $requestVoteKey, $requestValue);
                                $value = $requestValue;
                            } elseif (
                                $requestValue == -1
                                && $verifyVoteSubmit
                                && $voteExists
                            ) {
                                $vote = $event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->where('value', '>', '0')->first()->delete();
                                $value = 0;
                            } else {
                                $canVote = $positiveVote->canVote($userKey, $requestVoteKey, $requestValue);
                                $value = $requestValue;
                            }
                        }
                        $message = $positiveVote->getMessage();
                        break;
                    case 3:
                        $negativeVote = new NegativeVote($event);
                        if ($requestValue == 1 && $event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->where('value', '<', '0')->exists()) {
                            $canVote = $negativeVote->canVote($userKey, $requestVoteKey, $requestValue);
                            if ($canVote) {
                                $event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->where('value', '<', '0')->first()->delete();
                            }
                            $value = $requestValue;
                        } elseif ($requestValue == 1 && $event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->where('value', '>', '0')->exists() && $negativeVote->getConfigurationValues($this->configs['negativeVotes']['multi']) == 0) {
                            $event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->where('value', '>', '0')->first()->delete();
                            $value = 0;
                        } elseif ($requestValue == -1 && $event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->where('value', '>', '0')->exists()) {
                            $canVote = $negativeVote->canVote($userKey, $requestVoteKey, $requestValue);
                            if ($canVote) {
                                $event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->where('value', '>', '0')->first()->delete();
                            }
                            $value = $requestValue;
                        } elseif ($requestValue == -1 && $event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->where('value', '<', '0')->exists() && $negativeVote->getConfigurationValues($this->configs['negativeVotes']['multi']) == 0) {
                            $event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->where('value', '<', '0')->first()->delete();
                            $value = 0;
                        } else {
                            $canVote = $negativeVote->canVote($userKey, $requestVoteKey, $requestValue);
                            $value = $requestValue;
                        }
                        $message = $negativeVote->getMessage();
                        break;
                    case 4:
                        $rank = new RankVote($event);
                        if ($event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->exists()){
                            $event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->first()->delete();
                        }

                        $canVote = $rank->canVote($userKey, $requestVoteKey, $requestValue);
                        $value = $request->value;

                        $message = $rank->getMessage();
                        break;
                }
                \Log::info("VOTE [U:".$userKey."][V:".$requestVoteKey."] Received");
                if ($canVote) {
                    $newVote = $event->votes()->create(
                        [
                            'vote_key' => $requestVoteKey,
                            'value' => $requestValue,
                            'user_key' => $userKey,
                            'source' => $source
                        ]
                    );
                    \Log::info("VOTE [U:".$userKey."][V:".$requestVoteKey."] Success");
                } else if (!empty($message)) {
                    \Log::info("VOTE [U:".$userKey."][V:".$requestVoteKey."] Failure <".$message.">");
                    throw new Exception();
                } else{
                    \Log::info("VOTE [U:".$userKey."][V:".$requestVoteKey."] Failure <user cant vote>");
                }

                // Get votes count
                // and send it to EMPATIA
                // to be saved on the topic
                try{
                    $cachedData = json_decode($event->_count_votes ?? "{}");
                    $voteCount = $cachedData->topics->{$requestVoteKey} ?? 0;
                    $totalVotes = $cachedData->count->total ?? 0;
                    $totalUsers = $cachedData->count->total_users ?? 0;

                    //Option -> No Queue
                    Empatia::updateTopicVotesInfo($voteCount, $requestVoteKey, $eventKey, $totalVotes, $totalUsers);

                    /*
                     * Option -> With Queue (Do not delete, this was purposely commented)
                     * This is a working example of how to use queues to update the votes count
                    */
//                     $this->dispatch((new SaveVotesInTopic($voteCount, $requestVoteKey, $eventKey, $totalVotes, $totalUsers))->delay(Carbon::now()->addSecond(1)));

                } catch (Exception $e){
                    \Log::info("VOTE [U:".$userKey."][V:".$requestVoteKey."] Failure <".$e->getMessage().">");
                    // do nothing
                    // return response()->json($e->getMessage(), 500);
                }

                $totalSummary = [];
                switch ($event->method_id) {
                    case 1:
                        $total = $like->getTotalVotes();
                        $value = $like->getValueVote($userKey, $requestVoteKey);
                        $summary = null;
                        break;
                    case 2:
                        $response = $positiveVote->getVotesData($userKey, $requestVoteKey);
                        $total          = $response->totalVotes;
                        $value          = $response->valueVote;
                        $summary        = $response->remainingVotes;
                        $totalSummary   = $response->TotalSummary;
                        break;
                    case 3:
                        $total = $negativeVote->getTotalVotes();
                        $value = $negativeVote->getTotalVoted($userKey, $requestVoteKey);
                        $summary = $negativeVote->getRemainingVotes($userKey);
                        break;
                    case 4:
                        $total = $rank->getTotalVotes();
                        $value = $rank->getValueVote($userKey, $requestVoteKey);
                        $summary = null;
                        break;
                }
                \Log::info("VOTE [U:".$userKey."] UnLocked File");
                flock($file,LOCK_UN);

                return response()->json(['vote' => 'Ok', 'summary' => $summary, 'value' => $value, 'total_votes' => $total, 'total_summary' => $totalSummary, 'votes_count' => $voteCount], 200);
            }elseif($event->method_id == 2 && !empty($request->type_id)){
                $positiveVote = new MultiVote($event);
                $hasVoteType = true;

                $canVote = $positiveVote->canVote($userKey, $request->vote_key, $request->value);

//		if($event->votes()->whereUserKey($userKey)->where('vote_key', '!=', $request->vote_key)->where('value', '==', $request->value)){
//		    $canVote = false;
//        }
                if($event->votes()->where('vote_key', '!=', $request->vote_key)->whereUserKey($userKey)->where('value', '=', $request->value)->exists()){
                    $canVote = false;
                }
                if($event->votes()->whereVoteKey($request->vote_key)->whereUserKey($userKey)->whereVoteTypeId($request->type_id)->where('value', '>', '0')->exists()){
                    $event->votes()->whereVoteKey($request->vote_key)->whereUserKey($userKey)->whereVoteTypeId($request->type_id)->where('value', '>', '0')->first()->delete();
                    $value = 0;
                }

                if($canVote){
                    $newVote = $event->votes()->create(
                        [
                            'vote_key' => $request->vote_key,
                            'vote_type_id' => $request->type_id,
                            'value' => $request->value,
                            'user_key' => $userKey,
                            'source' => $request->source
                        ]
                    );

                    $value = $request->value;
                }

                try{
                    $cachedData = json_decode($event->_count_votes ?? "{}");
                    $voteCount = $cachedData->topics->{$request->vote_key} ?? 0;
                    $totalVotes = $cachedData->count->total ?? 0;
                    $totalUsers = $cachedData->count->total_users ?? 0;

                    //Option -> No Queue
                    Empatia::updateTopicVotesInfo($voteCount, $request->vote_key, $eventKey, $totalVotes, $totalUsers);

                    /*
                     * Option -> With Queue (Do not delete, this was purposely commented)
                     * This is a working example of how to use queues to update the votes count
                    */
//                     $this->dispatch((new SaveVotesInTopic($voteCount, $requestVoteKey, $eventKey, $totalVotes, $totalUsers))->delay(Carbon::now()->addSecond(1)));

                } catch (Exception $e){
                    \Log::info("VOTE [U:".$userKey."][V:".$request->vote_key."] Failure <".$e->getMessage().">");
                    // do nothing
                    // return response()->json($e->getMessage(), 500);
                }

                $totalSummary = [];
                switch ($event->method_id) {
                    case 1:
                        $total = $like->getTotalVotes();
                        $value = $like->getValueVote($userKey, $request->vote_key);
                        $summary = null;
                        break;
                    case 2:
                        $response = $positiveVote->getVotesData($userKey,  $request->vote_key);
                        $total          = $response->totalVotes;
                        $value          = $response->valueVote;
                        $summary        = $response->remainingVotes;
                        $totalSummary   = $response->TotalSummary;
                        break;
                    case 3:
                        $total = $negativeVote->getTotalVotes();
                        $value = $negativeVote->getTotalVoted($userKey,  $request->vote_key);
                        $summary = $negativeVote->getRemainingVotes($userKey);
                        break;
                    case 4:
                        $total = $rank->getTotalVotes();
                        $value = $rank->getValueVote($userKey,  $request->vote_key);
                        $summary = null;
                        break;
                }

                return response()->json(['vote' => 'Ok', 'summary' => $summary, 'value' => $value, 'total_votes' => $total, 'total_summary' => $totalSummary, 'votes_count' => $voteCount, 'has_vote_type' => $hasVoteType], 200);
            }

        } catch (Exception $e) {
            \Log::info("VOTE [U:".$userKey."][V:".!empty($request->json('vote_key')) ? $request->json('vote_key') : "vote_key_undefined"."] Failure <".$e->getMessage().">");
            \Log::info("VOTE [U:".$userKey."] UnLocked File");
            flock($file,LOCK_UN);
            if (!empty($message)) {
                return response()->json(['error' => $message], 500);
            } elseif ($e->getMessage() === 'Voting is closed') {
                return response()->json(['error' => 'vote_close'], 500);
            } else {
                dd($e->getMessage());
                return response()->json(['error' => 'store_vote'], 500);
            }
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }


    /**
     * Return the Attribute if can vote
     * @param Request $request
     * @return static
     */
    public function listVotes(Request $request, $eventKey)
    {
        $userKey = ONE::verifyToken($request);

        if(ONE::verifyRole($userKey,$request) == 'manager' || ONE::verifyRole($userKey,$request) == 'admin'){
            if(!empty($request->user_key)){
                $userKey = $request->user_key;
            }
        }


        try {
            $event = Event::where("key",$eventKey)->firstOrFail();
            $data = [];
            $voteKeys = $request->json('voteList');
            foreach ($voteKeys as $voteKey) {
                if (Vote::where('vote_key', '=', $voteKey)->exists()) {
                    switch ($event->method_id) {
                        case 1:
                            $like = new Like($event);
                            $canVotePositive = $like->canVote($userKey, $voteKey, 1);
                            $canVoteNegative = $like->canVote($userKey, $voteKey, -1);
                            $voteValue = $like->getValueVote($userKey, $voteKey);
                            break;
                        case 2:
                            $positiveVote = new MultiVote($event);
                            $canVotePositive = $positiveVote->canVote($userKey, $voteKey, 1);
                            $canVoteNegative = $positiveVote->canVote($userKey, $voteKey, -1);
                            $voteValue = $positiveVote->getValueVote($userKey, $voteKey);
                            break;
                        case 3:
                            $negativeVote = new NegativeVote($event);
                            $canVotePositive = $negativeVote->canVote($userKey, $voteKey, 1);
                            $canVoteNegative = $negativeVote->canVote($userKey, $voteKey, -1);
                            $voteValue = $negativeVote->getValueVote($userKey, $voteKey);
                            break;
                    }
                    $data[] = [
                        "proposal_key" => $voteKey,
                        "vote" => $voteValue,
                        "positive" => $canVotePositive,
                        "negative" => $canVoteNegative,
                    ];
                }
            }

            switch ($event->method_id) {
                case 1:
                    if(empty($data) ){
                        $like = new Like($event);
                    }
                    $votesRemaining = null;
                    break;
                case 2:
                    if(empty($data) ){
                        $positiveVote = new MultiVote($event);
                    }
                    $votesRemaining = $positiveVote->getRemainingVotes($userKey);
                    break;
                case 3:
                    if(empty($data) ){
                        $negativeVote = new NegativeVote($event);
                    }
                    $votesRemaining = $negativeVote->getRemainingVotes($userKey);
                    break;
            }
            return response()->json(["data" => $data, "remainingVotes" => $votesRemaining], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to show the Vote'], 500);
        }
    }

    /**
     * @param Request $request
     * @param $eventKey
     * @return \Illuminate\Http\JsonResponse
     */
    public function eventVotes(Request $request, $eventKey)
    {
        $userKey = ONE::verifyToken($request);

        if(ONE::verifyRole($userKey,$request) == 'manager' || ONE::verifyRole($userKey,$request) == 'admin'){
            if(!empty($request->user_key)){
                $userKey = $request->user_key;
            }
        }

        try {
            $event = Event::where("key",$eventKey)->firstOrFail();
            $voteKeys = $request->json('vote_keys');

            $voteDetails = [];

            foreach ($voteKeys as $voteKey) {

                switch ($event->method_id) {
                    case 1:
                        $like = new Like($event);
                        $canVotePositive = $like->canVote($userKey, $voteKey, 1);
                        $canVoteNegative = $like->canVote($userKey, $voteKey, -1);
                        $votesRemaining = null;
                        $voteValue = $like->getValueVote($userKey, $voteKey);
                        break;
                    case 2:
                        $positiveVote = new MultiVote($event);
                        $canVotePositive = $positiveVote->canVote($userKey, $voteKey, 1);
                        $canVoteNegative = $positiveVote->canVote($userKey, $voteKey, -1);
                        $votesRemaining = $positiveVote->getRemainingVotes($userKey);
                        $voteValue = $positiveVote->getValueVote($userKey, $voteKey);
                        break;
                    case 3:
                        $negativeVote = new NegativeVote($event);
                        $canVotePositive = $negativeVote->canVote($userKey, $voteKey, 1);
                        $canVoteNegative = $negativeVote->canVote($userKey, $voteKey, -1);
                        $votesRemaining = $negativeVote->getRemainingVotes($userKey);
                        $voteValue = $negativeVote->getValueVote($userKey, $voteKey);
                        break;
                }
                if ($votesRemaining['total'] == 0) {
                    $votesRemaining['negative'] = 0;
                }

                $response = [
                    "vote" => $voteValue,
                    "positive" => $canVotePositive,
                    "negative" => $canVoteNegative,
                    "remainingVotes" => $votesRemaining
                ];

                $voteDetails[$voteKey] = $response;
            }

            return response()->json(['data' => $voteDetails], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to show the Vote'], 500);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function voteTimeline(Request $request)
    {
        $userKey = ONE::verifyToken($request);

        if(ONE::verifyRole($userKey,$request) == 'manager' || ONE::verifyRole($userKey,$request) == 'admin'){
            if(!empty($request->user_key)){
                $userKey = $request->user_key;
            }
        }

        try {
            $votesDB = Vote::withTrashed()->whereUserKey($userKey)->get();
            $votes = [];
            foreach ($votesDB as $vote) {
                $votes[] = array_merge($vote->toArray(),["order_date"=>$vote->created_at]);
                if (!is_null($vote->deleted_at))
                    $votes[] = array_merge($vote->toArray(),["order_date"=>$vote->deleted_at]);
            }
            $votes = collect($votes)->sortByDesc("order_date");

            return response()->json(['data' => $votes], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to show the Vote'], 500);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDataForVoteCode(Request $request) {
        try {
            /* The format of the code is Defined at WUI: PublicCbsController@genericSubmitVotes */
            $voteCodeData = $request->voteCodeData;

            $votes = Event::whereId($voteCodeData[2])->firstOrFail()
                ->votes()->whereUserKey($voteCodeData[1])->whereUpdatedAt(Carbon::createFromTimestamp($voteCodeData[0]))
                ->get();

            if ($votes->count()==$voteCodeData[3])
                return response()->json(['votes'=>$votes->pluck("vote_key")]);
            else
                return response()->json(['error' => 'Votes count don\'t match'], 500);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to get data for Vote Code'], 500);
        }
    }

    // Note: This method is a copy of the store method. Any change made there should be replicated here.
    public function smsVote(Request $request) {
        try {
            $userKey = $request->get("user");
            $requestVoteKey = $request->get("vote");
            $eventKey = $request->get("event");
            $requestValue = $request->get("value");

            /* Get the Event */
            $event = Event::where("key","=",$eventKey)->first();
            if (!empty($event)) {
                /* The event method is recognized */
                if ($event->method_id == 1 || $event->method_id == 2 || $event->method_id == 3 || $event->method_id == 4) {
                    /* Check if the vote event is closed */
                    try {
                        $this->checkOpenVoting($event);
                    } catch(Exception $e) {
                        return response()->json(["code"=>-3, "error" => "voting closed"],400);
                    }

                    /* Check if the user can vote - Untouched code */
                    switch ($event->method_id) {
                        case 1:
                            $like = new Like($event);
                            $canVote = $like->canVote($userKey, $requestVoteKey,$requestValue);

                            if ($requestValue == 1 && $event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->where('value', '<', '0')->exists()) {
                                $event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->where('value', '<', '0')->first()->delete();
                            } elseif ($requestValue == 1 && $event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->where('value', '>', '0')->exists()) {
                                $event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->where('value', '>', '0')->first()->delete();
                                $value = 0;
                            } elseif ($requestValue == -1 && $event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->where('value', '>', '0')->exists() && $like->getConfigurationValues($this->configs['likes']['dislike']) == 1) {
                                $event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->where('value', '>', '0')->first()->delete();
                            } elseif ($requestValue == -1 && $event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->where('value', '<', '0')->exists() && $like->getConfigurationValues($this->configs['likes']['dislike']) == 1) {
                                $event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->where('value', '<', '0')->first()->delete();
                                $value = 0;
                            }

                            $message = $like->getMessage();
                            break;
                        case 2:
                            $positiveVote = new MultiVote($event);
                            $verifyVoteSubmit = $positiveVote->verifyVoteSubmit($userKey);
                            $voteExists = $event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->where('value', '>', '0')->exists();

                            if (
                                $requestValue == 1
                                && $voteExists
                                && $verifyVoteSubmit
                                && $positiveVote->getConfigurationValues($this->configs['positiveVotes']['multi'], $userKey) == 0
                            ) {
                                $event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->where('value', '>', '0')->first()->delete();
                                $value = 0;
                            } elseif (
                                $requestValue == 1
                                && $voteExists
                                && $positiveVote->getConfigurationValues($this->configs['positiveVotes']['multi'], $userKey) == 1
                            ) {
                                $canVote = $positiveVote->canVote($userKey, $requestVoteKey, $requestValue);
                                $value = $requestValue;
                            } elseif (
                                $requestValue == -1
                                && $verifyVoteSubmit
                                && $voteExists
                            ) {
                                $vote = $event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->where('value', '>', '0')->first()->delete();
                                $value = 0;
                            } else {
                                $canVote = $positiveVote->canVote($userKey, $requestVoteKey, $requestValue);
                                $value = $requestValue;
                            }
                            $message = $positiveVote->getMessage();
                            break;
                        case 3:
                            $negativeVote = new NegativeVote($event);
                            if ($requestValue == 1 && $event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->where('value', '<', '0')->exists()) {
                                $canVote = $negativeVote->canVote($userKey, $requestVoteKey, $requestValue);
                                if ($canVote) {
                                    $event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->where('value', '<', '0')->first()->delete();
                                }
                                $value = $requestValue;
                            } elseif ($requestValue == 1 && $event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->where('value', '>', '0')->exists() && $negativeVote->getConfigurationValues($this->configs['negativeVotes']['multi']) == 0) {
                                $event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->where('value', '>', '0')->first()->delete();
                                $value = 0;
                            } elseif ($requestValue == -1 && $event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->where('value', '>', '0')->exists()) {
                                $canVote = $negativeVote->canVote($userKey, $requestVoteKey, $requestValue);
                                if ($canVote) {
                                    $event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->where('value', '>', '0')->first()->delete();
                                }
                                $value = $requestValue;
                            } elseif ($requestValue == -1 && $event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->where('value', '<', '0')->exists() && $negativeVote->getConfigurationValues($this->configs['negativeVotes']['multi']) == 0) {
                                $event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->where('value', '<', '0')->first()->delete();
                                $value = 0;
                            } else {
                                $canVote = $negativeVote->canVote($userKey, $requestVoteKey, $requestValue);
                                $value = $requestValue;
                            }
                            $message = $negativeVote->getMessage();
                            break;
                        case 4:
                            $rank = new RankVote($event);
                            if ($event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->exists()){
                                $event->votes()->whereVoteKey($requestVoteKey)->whereUserKey($userKey)->first()->delete();
                            }

                            $canVote = $rank->canVote($userKey, $requestVoteKey, $requestValue);
                            $value = $request->value;

                            $message = $rank->getMessage();
                            break;
                    }

                    if ($canVote??false) {
                        $newVote = $event->votes()->create([
                            'vote_key' => $requestVoteKey,
                            'value' => $requestValue,
                            'user_key' => $userKey,
                            'submitted' => 1,
                            'source' => 'sms'
                        ]);
                    } else {
                        if ($event->votes()->whereSubmitted(1)->whereUserKey($userKey)->exists())
                            return response()->json(["code"=>-5, "error" => "already submitted"],400);

                        if (!empty($message))
                            return response()->json(["code"=>-4, "error" => "can't vote", "message" => $message],400);
                    }

                    // Get votes count
                    // and send it to EMPATIA
                    // to be saved on the topic
                    try{
                        $cachedData = json_decode($event->_count_votes ?? "{}");
                        $voteCount = $cachedData->topics->{$requestVoteKey} ?? 0;
                        $totalVotes = $cachedData->count->total ?? 0;
                        $totalUsers = $cachedData->count->total_users ?? 0;

                        //Option -> No Queue
                        Empatia::updateTopicVotesInfo($voteCount, $requestVoteKey, $eventKey, $totalVotes, $totalUsers);

                        /*
                        * Option -> With Queue (Do not delete, this was purposely commented)
                        * This is a working example of how to use queues to update the votes count
                        */
//                      $this->dispatch((new SaveVotesInTopic($voteCount, $requestVoteKey, $eventKey, $totalVotes, $totalUsers))->delay(Carbon::now()->addSecond(1)));

                    } catch (Exception $e){
                        // do nothing
                        // return response()->json($e->getMessage(), 500);
                    }

                    return response()->json(["code" => 1, "id" => $newVote->id],200);
                } else
                    return response()->json(["code"=>-2, "error" => "unrecognized event method"],400);
            } else
                return response()->json(["code"=>-1, "error" => "invalid event"],400);
        } catch(Exception $e) {
            return response()->json([
                'code' => -10,
                'error'=> 'Failed to Vote',
                'e-line'=> $e->getLine(),
                'e-file'=> $e->getFile(),
                'e'=> $e
            ], 500);
        }
    }

    public function userVotesCount(Request $request) {
        try {
            $voteKeys = $request->get("voteKeys",[]);

            $voteEvents = Event::whereIn("key",$request->get("voteEvents"))->get();

            $userVotesCountByVoteEvent = array();
            foreach ($voteEvents as $voteEvent) {
                if (!empty($voteKeys)) {
                    $userVotesCountByVoteEvent[$voteEvent->key]["positive"] = $voteEvent->votes()
                        ->select(\DB::raw("`user_key`, COUNT(*) as `votes_count`"))
                        ->where("value",">","0")
                        ->whereIn("vote_key",$voteKeys)
                        ->groupBy("user_key")
                        ->get()
                        ->pluck("votes_count","user_key");

                    $userVotesCountByVoteEvent[$voteEvent->key]["negative"] = $voteEvent->votes()
                        ->select(\DB::raw("`user_key`, COUNT(*) as `votes_count`"))
                        ->where("value","<","0")
                        ->whereIn("vote_key",$voteKeys)
                        ->groupBy("user_key")
                        ->get()
                        ->pluck("votes_count","user_key");
                } else {
                    $userVotesCountByVoteEvent[$voteEvent->key]["positive"] = $voteEvent->votes()
                        ->select(\DB::raw("`user_key`, COUNT(*) as `votes_count`"))
                        ->where("value",">","0")
                        ->groupBy("user_key")
                        ->get()
                        ->pluck("votes_count","user_key");

                    $userVotesCountByVoteEvent[$voteEvent->key]["negative"] = $voteEvent->votes()
                        ->select(\DB::raw("`user_key`, COUNT(*) as `votes_count`"))
                        ->where("value","<","0")
                        ->groupBy("user_key")
                        ->get()
                        ->pluck("votes_count","user_key");
                }
            }


            return response()->json($userVotesCountByVoteEvent,200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to get User Votes Count'], 500);
        }
    }

    public function deleteUserVotes(Request $request) {
        try {
            $userKey = ONE::verifyToken($request);
            \Log::info("[VOTES-ERROR] Received request to delete " . $userKey . " votes");
            $deletedVotesCount = 0;

            $events = Event::with([
                "votes" => function($q) use ($userKey) {
                    $q->where("user_key","=",$userKey);
                }
            ])
                ->whereIn("key",["GzrilShlzWd66O8RqU3uBLtikTiG8GZ0","Ncmktzk7vuFJiO2D7G4yJ5IvkpNSs3kY"])
                ->get();

            foreach ($events as $event) {
                foreach ($event->votes as $eventVote) {
                    $eventVote->delete();
                    $deletedVotesCount++;
                }
            }

            \Log::info("[VOTES-ERROR] Deleted " . $deletedVotesCount . " votes for " . $userKey);
            return response()->json(["success" => true, "deletedVotesCount" => $deletedVotesCount]);
        } catch(Exception $e) {
            \Log::info("[VOTES-ERROR] Exception while deleting User " . ($userKey??"no user key") . " votes");
            return response()->json(["failed" => true], 500);
        }
    }

    public function destroy($voteId) {
        try {
            $votes = Vote::where('id',$voteId)->delete();
            $votes = Vote::where('id',$voteId)->withTrashed()->first();
            $votes['submitted'] = 0;
            $votes->save();

            return response()->json(["success" => true]);
        } catch(Exception $e) {
            dd($e->getMessage());
            return response()->json(["failed" => true], 500);
        }
    }

    public function getVoteList(Request $request) {
        try {
            $role = $request->role;
            $event = Event::where('key', 'like', $request->voteKey)->first();
            $tableData = $request->input('tableData') ?? null;
            $votes = [];
            if(!is_null($event)){
                $eventId = $event->id;
                $voteKey = $request->filters['voteKey'];
                $deleted = $request->filters['deleted'];
                $submitted = $request->filters['submitted'];
                $source = $request->filters['source'];


                if($deleted == 1){
                    $votes = Vote::where('event_id',$eventId)
                    ->where('vote_key','like','%'.$voteKey.'%')
                    ->where('submitted',$submitted)
                    ->where('source','like','%'.$source.'%')
                    ->withTrashed();
                }else{
                    $votes = Vote::where('event_id',$eventId)
                    ->where('vote_key','like','%'.$voteKey.'%')
                    ->where('submitted',$submitted)
                    ->where('source','like','%'.$source.'%');
                }

                if($role != "admin"){
                    foreach($votes as $vote){
                        unset($vote['user_key']);
                        unset($vote['vote_key']);
                    }
                }
            }

            $recordsTotal = $votes->count();
            $query = $votes;

            $query = $query
                ->orderBy($tableData['order']['value'], $tableData['order']['dir']);

            if(!empty($tableData['search']['value'])) {
                $query = $query
                    ->where('id', 'like', '%'.$tableData['search']['value'].'%')
                    ->orWhere('user_key', 'like', '%'.$tableData['search']['value'].'%')
                    ->orWhere('user_name', 'like', '%'.$tableData['search']['value'].'%')
                    ->orWhere('topic_key', 'like', '%'.$tableData['search']['value'].'%')
                    ->orWhere('topic_name', 'like', '%'.$tableData['search']['value'].'%')
                    ->orWhere('created_at', 'like', '%'.$tableData['search']['value'].'%')
                    ->orWhere('deleted_at', 'like', '%'.$tableData['search']['value'].'%')
                    ->orWhere('source', 'like', '%'.$tableData['search']['value'].'%');
            }

            $recordsFiltered = $query->count();

            $votes = $query
                ->skip($tableData['start'])
                ->take($tableData['length'])
                ->get();

            $data['votes'] = $votes;
            $data['recordsTotal'] = $recordsTotal;
            $data['recordsFiltered'] = $recordsFiltered;

            return response()->json($data, 200);

        } catch(Exception $e) {
            dd($e->getMessage());
            return response()->json(['error' => 'Failed to get votes list'], 500);
        }
    }

    public function submitUserVote(Request $request)
    {
        try {
            $votes = $request->votes_id;
            if(!empty($request->submit) && $request->submit == "submit"){
                if(count($votes)>1){
                    foreach($votes as $vote){
                        $voteStatus = Vote::where('id',$vote)->first();
                        if($voteStatus['deleted_at'] == null){
                            $voteStatus['submitted'] = 1;
                            $voteStatus->save();
                        }else{
                            return response()->json(['error' => 'Failed to submit votes list'], 500);
                        }
                    }
                }else{
                    $voteStatus = Vote::where('id',$votes)->first();
                    if($voteStatus['deleted_at'] == null){
                        $voteStatus['submitted'] = 1;
                        $voteStatus->save();
                    }else{
                        return response()->json(['error' => 'Failed to submit vote'], 500);
                    }
                }
            }
            elseif(!empty($request->submit) && $request->submit == "unsubmit"){
                if(count($votes)>1){
                    foreach($votes as $vote){
                        $voteStatus = Vote::where('id',$vote)->first();
                        if($voteStatus['deleted_at'] == null){
                            $voteStatus['submitted'] = 0;
                            $voteStatus->save();
                        }else{
                            return response()->json(['error' => 'Failed to unsubmit votes list'], 500);
                        }
                    }
                }else{
                    $voteStatus = Vote::where('id',$votes)->first();
                    if($voteStatus['deleted_at'] == null){
                        $voteStatus['submitted'] = 0;
                        $voteStatus->save();
                    }else{
                        return response()->json(['error' => 'Failed to unsubmit vote'], 500);
                    }
                }
            }
            return response()->json(["success" => true]);
        }catch(Exception $e) {
            dd($e->getMessage());
            return response()->json(["failed" => true], 500);
        }
    }

    public function deleteVotes(Request $request)
    {
        try {
            $votes = $request->votes_id;
            foreach($votes as $vote){
                $voteDeleted = Vote::where('id',$vote)->delete();
                $voteDeleted = Vote::where('id',$vote)->first();
                $voteDeleted['submitted'] = 0;
                $voteDeleted->save();
            }
            return response()->json(["success" => true]);
        }catch(Exception $e) {
            return response()->json(["failed" => true], 500);
        }
    }
}
