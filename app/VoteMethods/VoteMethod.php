<?php

namespace App\VoteMethods;


abstract class VoteMethod{
    abstract protected function canVote($userKey, $voteKey, $voteValue);
}