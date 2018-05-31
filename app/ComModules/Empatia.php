<?php
/**
 * Created by PhpStorm.
 * User: nelson
 * Date: 30/08/2017
 * Time: 09:19
 */

namespace App\ComModules;
use App\One\One;
use Exception;

class Empatia
{
    public static function updateTopicVotesInfo($votes, $topicKey, $eventKey, $totalVotes, $totalUsers) {
        $response = ONE::put([
            'component'     => 'empatia',
            'api'           => 'topic',
            'api_attribute' => $topicKey,
            'method'        => 'updateTopicVotesInfo',
            'params'        => [
                'votes'         => $votes,
                'event_key'     => $eventKey,
                'total_votes'   => $totalVotes,
                'total_users'   => $totalUsers
            ]
        ]);
    }

    public static function checkIfUserHasAllLoginLevelsToVote($eventKey,$userKey,$entityKey,$languageCode) {
        $response = One::post([
            'component' => 'empatia',
            'api' => 'cb',
            'method' => 'checkIfUserHasAllLoginLevelsToVote',
            'params'=>[
                'event_key'=> $eventKey,
                'user_key' => $userKey,
                'entity_key' => $entityKey,
                'language_code' => $languageCode

            ]
        ]);

        if($response->statusCode() != 200){
            return false;
        }
        return true;
    }

    public static function getVoteEventConfigurations($voteKey) {
        $response = One::get([
            'component' => 'empatia',
            'api' => 'vote',
            'method' => $voteKey
        ]);

        if($response->statusCode() != 200){
            throw new Exception(trans("comModulesEMPATIA.failed_to_get_vote_event_configurations"));
        }
        return $response->json();
    }
}