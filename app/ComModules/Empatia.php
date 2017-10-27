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
}