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
            $event = Event::whereKey($eventKey)->firstOrFail();

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
            $event = Event::whereKey($eventKey)->firstOrFail();
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

    /**
     * Store a new Vote in the database
     * Return the Attributes of the Vote created
     * @param Request $request
     * @return static
     */
    public function store(Request $request)
    {
        $userKey = ONE::verifyToken($request);
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

        try {
            $eventKey = $request->json('event_key');
            $event = Event::whereKey($eventKey)->firstOrFail();
            if ((($event->method_id == 1 || $event->method_id == 2 || $event->method_id == 3) && ($request->json('value') == -1 || $request->json('value') == 1)) || $event->method_id == 4) {
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

                if ($canVote) {
                    $newVote = $event->votes()->create(
                        [
                            'vote_key' => $requestVoteKey,
                            'value' => $requestValue,
                            'user_key' => $userKey,
                            'source' => $source
                        ]
                    );
                } else if (!empty($message)) {
                    throw new Exception();
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
                    // do nothing
                    // return response()->json($e->getMessage(), 500);
                }

                $totalSummary = [];
                switch ($event->method_id) {
                    case 1:
                        $total = $like->getTotalVotes();
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
                        $summary = null;
                        break;
                }

                return response()->json(['vote' => 'Ok', 'summary' => $summary, 'value' => $value, 'total_votes' => $total, 'total_summary' => $totalSummary, 'votes_count' => $voteCount], 200);
            }
        } catch (Exception $e) {
            if (!empty($message)) {
                return response()->json(['error' => $message], 500);
            } elseif ($e->getMessage() === 'Voting is closed') {
                return response()->json(['error' => 'vote_close'], 500);
            } else {
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
            $event = Event::whereKey($eventKey)->firstOrFail();
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
            $event = Event::whereKey($eventKey)->firstOrFail();
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
}
