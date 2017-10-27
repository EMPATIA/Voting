<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserEventCode extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_key', 'event_key', 'code'];
}
