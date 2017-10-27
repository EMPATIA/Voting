<?php

namespace App\VoteMethods;

use App\Configuration;
use App\Event;
use App\GeneralConfig;
use App\One\One;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class MultiVote extends VoteMethod{

    private $event;

    private $configs = [
        'AllowMulti' => 'allow_multiple_per_one',
        'TotalVotes' => 'total_votes_allowed'
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
    public function canVote($userKey, $voteKey, $voteValue)
    {
        $vote = $this->event->votes()->whereUserKey($userKey)->whereVoteKey($voteKey)->first();

        $numVotes = 0;
        if(!empty($vote)) {
            $numVotes = $vote->value;
        }

        $data = $this->multiData($userKey);

        if ( ((!$data->allowMulti && $numVotes == 0) || ($data->allowMulti)) && $this->verifyVoteSubmit($userKey) ){
            if ($data->totalVotes){
                if($data->configurationValues < 0 || (($data->votesCount < $data->configurationValues) && $voteValue > 0) ){
                    return true;
                }elseif ($data->votesCount > 0 && $voteValue > 0){
                    return true;
                }
            }elseif (!$data->totalVotes && $voteValue < 0){
                if ($data->votesCount > 0 && $voteValue > 0){
                    return true;
                }
            }
            $this->message = "no_vote_available";
            return false;
        }
        $this->message = "can_not_vote";
        return false;
    }

    /**
     * @param $userKey
     * @return bool
     */
    private function totalVotes($userKey){
        $config = Configuration::whereCode($this->configs['TotalVotes'])->whereMethodId($this->event->method_id)->first();
        $configEvent = $this->event->configurationEvents()->whereConfigurationId($config->id)->whereGeneralConfigId($this->getConfigurationAdvance($userKey))->first();
        if($configEvent != null){
            $maxVotes = $configEvent->value;
            if($maxVotes <= $this->event->votes()->whereUserKey($userKey)->where('value', '!=', '0')->count() && $maxVotes>0){
                return false;
            }else{
                return true;
            }
        }
    }

    private function allowMulti($userKey){
        $config = Configuration::whereCode($this->configs['AllowMulti'])->whereMethodId($this->event->method_id)->first();
        $configEvent = $this->event->configurationEvents()->whereConfigurationId($config->id)->whereGeneralConfigId($this->getConfigurationAdvance($userKey))->first();
        if($configEvent != null && $configEvent->value == 1){
            return true;
        }
        return false;
    }


    /**
     * @param $userKey
     * @return object
     */
    private function multiData($userKey){
        $config = Configuration::whereCode($this->configs['TotalVotes'])->whereMethodId($this->event->method_id)->first();
        $configEvent = $this->event->configurationEvents()->whereConfigurationId($config->id)->whereGeneralConfigId($this->getConfigurationAdvance($userKey))->first();

        $response['configurationValues'] = $configEvent->value;
        $response['votesCount'] = $this->event->votes()->whereUserKey($userKey)->where('value', '!=', '0')->count();

        if($configEvent != null){
            $maxVotes = $configEvent->value;
            if($maxVotes <= $response['votesCount'] && $maxVotes>0){
                $response['totalVotes'] = false;
            }else{
                $response['totalVotes'] = true;
            }
        }

        $config = Configuration::whereCode($this->configs['AllowMulti'])->whereMethodId($this->event->method_id)->first();
        $configEvent = $this->event->configurationEvents()->whereConfigurationId($config->id)->whereGeneralConfigId($this->getConfigurationAdvance($userKey))->first();

        if($configEvent != null && $configEvent->value == 1){
            $response['allowMulti'] = true;
        } else{
            $response['allowMulti'] = false;
        }
        return (object)$response;
    }


    public function getRemainingVotes($userKey){
        $remainingVotes = [
            "total"     => 0,
            "user_votes" => 0
        ];
        /*Verify Total remaining*/
        /*TODO verify configuration type*/
        $config = Configuration::whereCode($this->configs['TotalVotes'])->whereMethodId($this->event->method_id)->first();
        $configEvent = $this->event->configurationEvents()->whereConfigurationId($config->id)->whereGeneralConfigId($this->getConfigurationAdvance($userKey))->first();
        if($configEvent != null){
            $votesUsed = $this->event->votes()->whereUserKey($userKey)->where('value', '!=', '0')->count();
            $totalVotes = $this->getConfigurationValues('TotalVotes', $userKey);
            $remainingVotes['total'] = $totalVotes - $votesUsed;
            $remainingVotes['user_votes'] = $votesUsed;
        }
        return $remainingVotes;
    }

    /**
     * @param $configType
     * @param $userKey
     * @return mixed
     */
    public function getConfigurationValues($configType, $userKey){
        $config = Configuration::whereCode($this->configs[$configType])->whereMethodId($this->event->method_id)->first();
        $configEvent = $this->event->configurationEvents()->whereConfigurationId($config->id)->whereGeneralConfigId($this->getConfigurationAdvance($userKey))->first();
        return $configEvent->value;
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

    public function getMessage(){
        return $this->message;
    }

    /**
     * @param $userKey
     * @param $voteKey
     * @return int
     */
    public function getTotalVoted($userKey, $voteKey){
        $vote = $this->event->votes()->whereUserKey($userKey)->whereVoteKey($voteKey)->first();
        if(!$this->allowMulti($userKey)){
            if(!empty($vote)){
                $valueVote = $vote->value;
            }
            else{
                $valueVote = 0;
            }
        }else{
            $total = $this->event->votes()->select(DB::raw('SUM(value) as value'))->whereUserKey($userKey)->whereVoteKey($voteKey)->groupBy('vote_key')->first();
            if (!empty($total->value)){
                $valueVote = $total->value;
            }
            else{
                $valueVote = 0;
            }
        }
        return $valueVote;
    }

    /**
     * @return mixed
     */
    public function getTotalVotes(){
        $data = $this->event->votes()->groupBy('vote_key')->get()->keyBy('vote_key');
        $positiveVotesTopics = array_count_values($this->event->positiveVotes()->get()->pluck('vote_key')->toArray());
        $negativeVotesTopics = array_count_values($this->event->negativeVotes()->get()->pluck('vote_key')->toArray());

        foreach ($data as $key => $item){
            $data[$key]['positive'] = isset($positiveVotesTopics[$key]) ? $positiveVotesTopics[$key]: 0;
            $data[$key]['negative'] = isset($negativeVotesTopics[$key]) ? $negativeVotesTopics[$key] : 0;
        }
        return $data;
    }

    /**
     * @return array
     */
    public function getTotalSummary(){
        $dateStart = Carbon::today();
        $dateEnd = Carbon::today()->addHour(20);
        $votedKeys = $this->event->votes()->whereSubmitted(1)->distinct()->pluck('vote_key');
        $dataVotes = $this->event->votes()->select(DB::raw(' (case when value > 0 then SUM(value) else 0 end) as positive,  (case when value < 0 then  ABS(SUM(value)) else 0 end) as negative, vote_key'))->whereSubmitted(1)->where('source' , '!=', 'in_person')->groupBy('vote_key')->get()->keyBy('vote_key')->toArray();
        $dataVotesInPerson = $this->event->votes()->select(DB::raw(' (case when value > 0 then SUM(value) else 0 end) as positive,  (case when value < 0 then  ABS(SUM(value)) else 0 end) as negative, vote_key'))->whereSubmitted(1)->where('source' , '=', 'in_person')->whereNotBetween('created_at' , [$dateStart,$dateEnd])->groupBy('vote_key')->get()->keyBy('vote_key')->toArray();
        $totalVotes = [];
        foreach ($votedKeys as $votedKey){
            $totalInPerson = 0;
            $totalGeneral = 0;
            if(!empty($dataVotes[$votedKey])){
                $totalGeneral= $dataVotes[$votedKey]['positive'];
            }
            if (!empty($dataVotesInPerson[$votedKey])){
                $totalInPerson = $dataVotesInPerson[$votedKey]['positive'] * 2;
            }
            $totalVotes[$votedKey]['positive'] = $totalGeneral + $totalInPerson;
            $totalVotes[$votedKey]['negative'] = 0;
        }
        return $totalVotes;
    }


    private function getConfigurationAdvance($userKey){
        $response = ONE::get([
            'url'       => env('USER_DATA_API') .'/'.$userKey
        ]);
        $userData = json_decode(json_encode($response->json()->user), true);

        $advanceConfigs = $this->event->configurationEvents()->get()->toArray();

        foreach ($advanceConfigs as $advanceConfig){
            if(!empty($advanceConfig['general_config_id'])){
                $generalConfig = GeneralConfig::whereId($advanceConfig['general_config_id'])->with('generalConfigType')->first();

                switch ($generalConfig->generalConfigType->code){
                    case 'minimum_age':
                        $date = !empty($userData['user_parameters'][$generalConfig['parameter_key']][0]['value']) ? $userData['user_parameters'][$generalConfig['parameter_key']][0]['value']: 0 ;
                        if (($date != 0) && (Carbon::parse($date)->diff(Carbon::now())->format('%y') < $generalConfig->value)){
                            return $generalConfig->id;
                        }
                        break;
                    case 'age':
                        if(!empty($userData['user_parameters'][ $generalConfig['parameter_key']][0]['value']) ){
                            $date = $userData['user_parameters'][ $generalConfig['parameter_key']][0]['value'];
                            if ($this->getAgeIn($generalConfig,  $date)){
                                return $generalConfig->id;
                            }
                        }
                        break;
                }
            }
        }
        return null;
    }

    private function getAgeIn($generalConfig, $value){
        $age = Carbon::parse($value)->diff(Carbon::now())->format('%y');
        $configAge = $generalConfig['value'];
        if($generalConfig['greater']){
            if ( $age >$configAge )
                return true;
        }else if($generalConfig['equal']){
            if ( $age = $configAge )
                return true;
        }else if($generalConfig['less']){
            if ( $age < $configAge )
                return true;
        }
        return false;
    }

    /**
     * @param $userKey
     * @return bool
     */
    public function verifyVoteSubmit($userKey){
        $vote = $this->event->votes()->whereUserKey($userKey)->whereSubmitted(1)->exists();

        if (!$vote){
            return true;
        }
        return false;
    }

    /**
     * @param $userKey
     * @return null
     */
    public function verifyVoteSubmitDate($userKey)
    {
        $vote = $this->event->votes()->whereUserKey($userKey)->whereSubmitted(1)->first();
        return $vote->updated_at ?? null;

    }

    /**
     * @param $userKey
     * @param $voteKey
     * @return object
     */
    public function getVotesData($userKey, $voteKey)
    {
        $remainingVotes = [
            "total"     => 0,
            "user_votes" => 0
        ];

        /*Verify Total remaining*/
        $config = Configuration::whereCode($this->configs['TotalVotes'])->whereMethodId($this->event->method_id)->first();
        $configEvent = $this->event->configurationEvents()->whereConfigurationId($config->id)->whereGeneralConfigId($this->getConfigurationAdvance($userKey))->first();

        if($configEvent != null){
            $remainingVotes['user_votes'] = $this->event->votes()->whereUserKey($userKey)->where('value', '!=', '0')->count();
            $totalVotes = $configEvent->value;
            $remainingVotes['total'] = $totalVotes - $remainingVotes['user_votes'];
        }
        $response['remainingVotes'] = $remainingVotes;

        $getTotalVotes = $this->event->votes()->groupBy('vote_key')->get()->keyBy('vote_key');
        $positiveVotesTopics = array_count_values($this->event->positiveVotes()->get()->pluck('vote_key')->toArray());
        $negativeVotesTopics = array_count_values($this->event->negativeVotes()->get()->pluck('vote_key')->toArray());

        foreach ($getTotalVotes as $key => $item){
            $getTotalVotes[$key]['positive'] = isset($positiveVotesTopics[$key]) ? $positiveVotesTopics[$key]: 0;
            $getTotalVotes[$key]['negative'] = isset($negativeVotesTopics[$key]) ? $negativeVotesTopics[$key] : 0;
        }

        $response['totalVotes'] =  $getTotalVotes;

        $config = Configuration::whereCode($this->configs['AllowMulti'])->whereMethodId($this->event->method_id)->first();
        $configEvent = $this->event->configurationEvents()->whereConfigurationId($config->id)->whereGeneralConfigId($this->getConfigurationAdvance($userKey))->first();

        $vote = $this->event->votes()->whereUserKey($userKey)->whereVoteKey($voteKey)->first();

        $valueVote = 0;
        if(!($configEvent != null && $configEvent->value == 1) && !empty($vote)){
            $valueVote = $vote->value;
        }else{
            $total = $this->event->votes()->select(DB::raw('SUM(value) as value'))->whereUserKey($userKey)->whereVoteKey($voteKey)->groupBy('vote_key')->first();
            if (!empty($total->value)){
                $valueVote = $total->value;
            }
        }
        $response['valueVote'] = $valueVote;

        $dateStart = Carbon::today();
        $dateEnd = Carbon::today()->addHour(20);
        $votedKeys = $this->event->votes()->whereSubmitted(1)->distinct()->pluck('vote_key');
        $dataVotes = $this->event->votes()->select(DB::raw(' (case when value > 0 then SUM(value) else 0 end) as positive,  (case when value < 0 then  ABS(SUM(value)) else 0 end) as negative, vote_key'))->whereSubmitted(1)->where('source' , '!=', 'in_person')->groupBy('vote_key')->get()->keyBy('vote_key')->toArray();
        $dataVotesInPerson = $this->event->votes()->select(DB::raw(' (case when value > 0 then SUM(value) else 0 end) as positive,  (case when value < 0 then  ABS(SUM(value)) else 0 end) as negative, vote_key'))->whereSubmitted(1)->where('source' , '=', 'in_person')->whereNotBetween('created_at' , [$dateStart,$dateEnd])->groupBy('vote_key')->get()->keyBy('vote_key')->toArray();
        $TotalSummary = [];
        foreach ($votedKeys as $votedKey){
            $totalInPerson = 0;
            $totalGeneral = 0;
            if(!empty($dataVotes[$votedKey])){
                $totalGeneral= $dataVotes[$votedKey]['positive'];
            }
            if (!empty($dataVotesInPerson[$votedKey])){
                $totalInPerson = $dataVotesInPerson[$votedKey]['positive'] * 2;
            }
            $TotalSummary[$votedKey]['positive'] = $totalGeneral + $totalInPerson;
            $TotalSummary[$votedKey]['negative'] = 0;
        }

        $response['TotalSummary'] = $TotalSummary;

        return (object)$response;
    }

}