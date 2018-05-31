<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vote extends Model
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['vote_type_id','user_key', 'event_id', 'vote_key', 'value', 'submitted', 'source'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * An Vote belongs to  Event
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function event(){
        return $this->belongsTo('App\Event');
    }
    /**
     * An Vote belongs to  Event
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function voteType(){
        return $this->belongsTo('App\VoteType');
    }
}
