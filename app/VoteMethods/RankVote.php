<?php

namespace App\VoteMethods;

use App\Configuration;
use App\Event;
use Illuminate\Support\Facades\DB;


class RankVote extends VoteMethod{

    private $event;

    private $configs = [
        'RangeStart' => 'rank_range_start',
        'RangeEnd' => 'rank_range_end'
    ];
    
    private $message = "";

    function __construct(Event $event){
        $this->event = $event;
    }

    public function canVote($userKey, $voteKey, $voteValue){
        $rangeStart = $this->getConfigurationValues('RangeStart');
        $rangeEnd = $this->getConfigurationValues('RangeEnd');
        if ($voteValue>=$rangeStart && $voteValue<=$rangeEnd)
            return true;


        $this->message = "Rank out of valid range";
        return false;
    }

    public function getValueVote($userKey, $voteKey){
        $vote = $this->event->votes()->whereUserKey($userKey)->whereVoteKey($voteKey)->first();
        if(!empty($vote))
            return $vote->value;
        else
            return 0;
    }

    public function getConfigurationValues($configType){
        $config = Configuration::whereCode($this->configs[$configType])->whereMethodId($this->event->method_id)->first();
        $configEvent = $this->event->configurationEvents()->whereConfigurationId($config->id)->first();
        return $configEvent->value;
    }

    public function getMessage(){
        return $this->message;
    }

    public function getTotalVotes(){
        $data = $this->event->votes()->groupBy('vote_key')->get()->keyBy('vote_key');
        $positiveVotes =  $this->event->positiveVotes()->get();

        $votesKeys = $positiveVotes->pluck('vote_key')->toArray();
        $positiveVotesTopics = array_count_values($votesKeys);

        foreach ($data as $key => $item){
            $data[$key] = isset($positiveVotesTopics[$key]) ?  $positiveVotesTopics[$key]: 0;
        }

        return $data;
    }
}