<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key',
        'name',
        'code',
        'method_id',
        'created_by',
        'start_date',
        'end_date',
        'start_time',
        'end_time'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['start_date', 'end_date','deleted_at'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['deleted_at'];

    /**
     * A Event belongs to  Method
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function method(){
        return $this->belongsTo('App\Method');
    }

    /**
     * A Event has many Votes
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function votes(){
        return $this->hasMany('App\Vote');
    }

    public function positiveVotes()
    {
        return $this->hasMany('App\Vote')->where('value', '>' , 0);
    }

    public function negativeVotes()
    {
        return $this->hasMany('App\Vote')->where('value', '<' , 0);
    }

    /**
     * A Event has many Configurations
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function configurationEvents(){
        return $this->hasMany('App\ConfigurationEvent');
    }

    public function eventLevels(){
        return $this->belongsTo('App\EventLevel');
    }
}
