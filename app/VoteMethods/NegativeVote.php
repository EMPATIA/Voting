<?php

namespace App\VoteMethods;

use App\Configuration;
use App\Event;
use Illuminate\Support\Facades\DB;


class NegativeVote extends VoteMethod{

    private $event;

    private $configs = [
        'AllowMulti' => 'allow_multiple_per_one',
        'TotalVotes' => 'total_votes_allowed',
        'MaxPositive'=> 'total_positive_votes_allowed',
        'MaxNegative'=> 'total_negative_votes_allowed'
    ];
    
    private $message = "";

    function __construct(Event $event){
        $this->event = $event;
    }
    
    public function canVote($userKey, $voteKey, $voteValue){
        
        // Check if the user voted!        
        $vote = $this->event->votes()->whereUserKey($userKey)->whereVoteKey($voteKey)->first();
        if(!empty($vote)){
            // Already voted
            $numVotes = $vote->value;
        }else{
            // The user didn't vote yet!
            $numVotes = 0;
        }

        // The user can vote If:
            // The user didn't vote yet OR this allow multi voting
        if ( (!$this->allowMulti() && $numVotes == 0) || $this->allowMulti()){
            if ($this->totalVotes($userKey)){
                // The user can vote If didn't reach is maximum votes for positives
                if($voteValue > 0) {
                    if ($this->event->votes()->whereUserKey($userKey)->whereVoteKey($voteKey)->where('value', '<', 0)->exists()) {
                        return false;
                    } elseif (($this->maxPositive($userKey))) {
                        return true;
                    } elseif ((!$this->maxPositive($userKey))) {
                        $this->message = "You don't have more votes!";
                        return false;
                    }
                }elseif($voteValue < 0) {
                    if($this->event->votes()->whereUserKey($userKey)->whereVoteKey($voteKey)->where('value','>', 0)->exists())  {
                        return false;
                    }elseif ($this->maxNegative($userKey) ){
                        return true;
                    }elseif ( !$this->maxNegative($userKey) && $voteValue < 0){
                        $this->message = "You don't have more negative votes!";
                        return false;
                    }
                }

            }
            // Generic message
            $this->message = "You don't have more votes!";
            return false;
        // If this Event don't allow Multi and you can vote POSITIVE yet
        }elseif ($this->getValueVote($userKey, $voteKey) < 0 && $this->maxPositive($userKey) && $voteValue > 0){
            return True;
        // If this Event don't allow Multi and you can NOT vote POSITIVE anymore
        }elseif ($this->getValueVote($userKey, $voteKey) < 0 && !$this->maxPositive($userKey) && $voteValue > 0){
            $this->message = "You don't have more votes!";
            return false;            
        // If this Event don't allow Multi and you can vote NEGATIVE yet            
        }elseif ($this->getValueVote($userKey, $voteKey) > 0 && $this->maxNegative($userKey) && $voteValue < 0){
            return True;
        // If this Event don't allow Multi and you can NOT vote NEGATIVE anymore            
        }elseif ($this->getValueVote($userKey, $voteKey) > 0 && !$this->maxNegative($userKey) && $voteValue < 0){
            $this->message = "You don't have more negative votes!";
            return false;
        }

        // Generic message!
        $this->message = "You can't vote!";        
        return false;
    }

    public function getValueVote($userKey, $voteKey){
        $vote = $this->event->votes()->whereUserKey($userKey)->whereVoteKey($voteKey)->first();
        if(!empty($vote)){
            $valueVote = $vote->value;
        }else{
            $valueVote = 0;
        }
        return $valueVote;
    }

    /**
     * @param $configType
     * @return mixed
     */
    public function getConfigurationValues($configType){
        $config = Configuration::whereCode($this->configs[$configType])->whereMethodId($this->event->method_id)->first();
        $configEvent = $this->event->configurationEvents()->whereConfigurationId($config->id)->first();
        return $configEvent->value;
    }

    /**
     * @param $userKey
     * @return array
     */
    public function getRemainingVotes($userKey){
        $remainingVotes = [
            "total"     => 0,
            "positive"  => 0,
            "negative"  => 0
        ];
        /*Verify Total remaining*/
        $config = Configuration::whereCode($this->configs['TotalVotes'])->whereMethodId($this->event->method_id)->first();
        $configEvent = $this->event->configurationEvents()->whereConfigurationId($config->id)->first();
        if($configEvent != null){
            $votesUsed = $this->event->votes()->whereUserKey($userKey)->where('value', '!=', '0')->count();

            $totalVotes = $this->getConfigurationValues('TotalVotes');
            $remainingVotes['total'] = $totalVotes - $votesUsed;
        }

        /*Verify Total positive remaining*/
        $config = Configuration::whereCode($this->configs['MaxPositive'])->whereMethodId($this->event->method_id)->first();
        $configEvent = $this->event->configurationEvents()->whereConfigurationId($config->id)->first();
        if($configEvent != null){
            $votesUsed = $this->event->votes()->whereUserKey($userKey)->where('value', '>', '0')->count();
            $totalVotes = $this->getConfigurationValues('MaxPositive');
            $remainingVotes['positive'] = $totalVotes - $votesUsed;
        }


        $config = Configuration::whereCode($this->configs['MaxNegative'])->whereMethodId($this->event->method_id)->first();
        $configEvent = $this->event->configurationEvents()->whereConfigurationId($config->id)->first();
        if($configEvent != null){
            if ($remainingVotes['total'] != 0) {
                $votesUsed = $this->event->votes()->whereUserKey($userKey)->where('value', '<', '0')->count();
                $totalVotes = $this->getConfigurationValues('MaxNegative');
                $remainingVotes['negative'] = $totalVotes - $votesUsed;
            }
        }
        return $remainingVotes;
    }


    private function allowMulti(){

        $config = Configuration::whereCode($this->configs['AllowMulti'])->whereMethodId($this->event->method_id)->first();
        $configEvent = $this->event->configurationEvents()->whereConfigurationId($config->id)->first();
        if($configEvent != null && $configEvent->value == 1){
            return true;
        }
        return false;
    }

    private function totalVotes($userKey){
        $config = Configuration::whereCode($this->configs['TotalVotes'])->whereMethodId($this->event->method_id)->first();
        $configEvent = $this->event->configurationEvents()->whereConfigurationId($config->id)->first();
        if($configEvent != null){
            $maxVotes = $configEvent->value;
            if($maxVotes <= $this->event->votes()->whereUserKey($userKey)->where('value', '!=', '0')->count()){
                return false;
            }else{
                return true;
            }
        }
    }

    private function maxPositive($userKey){
        $config = Configuration::whereCode($this->configs['MaxPositive'])->whereMethodId($this->event->method_id)->first();
        $configEvent = $this->event->configurationEvents()->whereConfigurationId($config->id)->first();
        if($configEvent != null){
            $maxVotes = $configEvent->value;
            if($maxVotes <= $this->event->votes()->whereUserKey($userKey)->where('value', '>', '0')->count()){
                return false;
            }else{
                return true;
            }
        }else{
            return $this->totalVotes($userKey);
        }
    }

    private function maxNegative($userKey){
        $config = Configuration::whereCode($this->configs['MaxNegative'])->whereMethodId($this->event->method_id)->first();
        $configEvent = $this->event->configurationEvents()->whereConfigurationId($config->id)->first();
        if($configEvent != null){
            $maxVotes = $configEvent->value;
            if ($maxVotes <= $this->event->votes()->whereUserKey($userKey)->where('value', '<', '0')->count()) {
                return false;
            } else {
                return true;
            }
        }else {
            return $this->totalVotes($userKey);
        }
    }
    
    public function getMessage(){
        return $this->message;
    }


    public function getTotalVoted($userKey, $voteKey){
        $vote = $this->event->votes()->whereUserKey($userKey)->whereVoteKey($voteKey)->first();
        if(!$this->allowMulti()){
            if (!empty($vote)){
                $valueVote = $vote->value;
            }
            else{
                $valueVote = 0;
            }
        }else{
            $total = $this->event->votes()->select(DB::raw('SUM(value) as value'))->whereUserKey($userKey)->whereVoteKey($voteKey)->groupBy('vote_key')->first();
            if (!empty($total)){
                $valueVote = $total->value;
            }
            else{
                $valueVote = 0;
            }
        }
        return $valueVote;
    }

    public function getTotalVotes(){
        $data = $this->event->votes()->groupBy('vote_key')->get()->keyBy('vote_key');
        $positiveVotes =  $this->event->positiveVotes()->get();
        $negativeVotes =  $this->event->negativeVotes()->get();

        $positiveVotesKeys = $positiveVotes->pluck('vote_key')->toArray();
        $negativeVotesKeys = $negativeVotes->pluck('vote_key')->toArray();
        $positiveVotesTopics = array_count_values($positiveVotesKeys);
        $negativeVotesTopics = array_count_values($negativeVotesKeys);

        foreach ($data as $key => $item){
            $data[$key]['positive'] = isset($positiveVotesTopics[$key]) ?  $positiveVotesTopics[$key]: 0;
            $data[$key]['negative']  = isset($negativeVotesTopics[$key]) ? $negativeVotesTopics[$key] : 0;
        }

        // $data = $this->event->votes()->select(DB::raw(' (case when value > 0 then SUM(value) else value end) as positive,  (case when value < 0 then  ABS(SUM(value)) else value end) as negative, vote_key'))->groupBy('vote_key')->get()->keyBy('vote_key');
        return $data;
    }

    public function verifyVoteSubmit($userKey){
        $vote = $this->event->votes()->whereUserKey($userKey)->whereSubmitted(1)->get();
        if (!empty($vote)){
            return true;
        }
        return false;

    }
}