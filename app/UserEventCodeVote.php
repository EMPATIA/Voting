<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserEventCodeVote extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_event_code_id', 'vote_key','created_by','updated_by'];
}
