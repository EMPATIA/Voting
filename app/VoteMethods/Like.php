<?php

namespace App\VoteMethods;

use App\Configuration;
use App\Event;
use Illuminate\Support\Facades\DB;


class Like extends VoteMethod{

    private $event;

    private $configs = [
        'AllowDislike'  => 'allow_dislike'
    ];

    private $message = "";

    function __construct(Event $event){
        $this->event = $event;
    }

    /**
     * @param $userKey
     * @param $voteKey
     * @param $voteValue
     * @return bool
     */
    public function canVote($userKey, $voteKey, $voteValue){
        $vote = $this->event->votes()->whereUserKey($userKey)->whereVoteKey($voteKey)->first();
        if(!empty($vote)){
            $numVotes = $vote->value;
        }else{
            $numVotes = 0;
        }

        if ( (!$this->allowDislike() && $numVotes == 0) || ($this->allowDislike()) ){
            if($voteValue > 0 && $voteValue!=$numVotes) {
                return true;
            }elseif ($voteValue < 0 && $voteValue!=$numVotes){
                return true;
            }
        }


        /*
        if ( (!$this->allowDislike() && $numVotes == 0) || ($this->allowDislike()) ){
            if($voteValue > 0 && $voteValue!=$numVotes) {
                return true;
            }elseif ($voteValue < 0 && $voteValue!=$numVotes){
                return true;
            } else if($voteValue > 0 && $voteValue==$numVotes) {
                return false;
            } else if($voteValue < 0 && $voteValue==$numVotes) {
                return false;
            }
        }
        */

        return false;
    }

    public function allowDislike(){
        $config = Configuration::whereCode($this->configs['AllowDislike'])->whereMethodId($this->event->method_id)->first();

        $configEvent = $this->event->configurationEvents()->whereConfigurationId($config->id)->first();
        if($configEvent != null && $configEvent->value == 1){
            return true;
        }
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

    public function getConfigurationValues($configType){
        $config = Configuration::whereCode($this->configs[$configType])->whereMethodId($this->event->method_id)->first();
        $configEvent = $this->event->configurationEvents()->whereConfigurationId($config->id)->first();
        return $configEvent->value;
    }

    public function getMessage(){
        return $this->message;
    }

    /**
     * @return mixed
     */
    public function getTotalVotes(){

        $data = $this->event->votes()->groupBy('vote_key')->get()->keyBy('vote_key');

        $positiveVotesKeys = $this->event->positiveVotes()->get()->pluck('vote_key')->toArray();
        $negativeVotesKeys = $this->event->negativeVotes()->get()->pluck('vote_key')->toArray();
        $positiveVotesTopics = array_count_values($positiveVotesKeys);
        $negativeVotesTopics = array_count_values($negativeVotesKeys);

        foreach ($data as $key => $item){
            $data[$key]['positive'] = isset($positiveVotesTopics[$key]) ?  $positiveVotesTopics[$key]: 0;
            $data[$key]['negative']  = isset($negativeVotesTopics[$key]) ? $negativeVotesTopics[$key] : 0;
        }

        return $data;
    }
}