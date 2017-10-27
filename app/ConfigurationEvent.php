<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConfigurationEvent extends Model
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['configuration_event_key', 'configuration_id', 'event_id', 'generic_config_id', 'value', 'created_by'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['deleted_at'];


    /**
     * A Configuration Events has many Events
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function event(){
        return $this->belongsTo('App\Event');
    }

    /**
     * A Configuration Event belongs to a Configuration
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function configuration(){
        return $this->belongsTo('App\Configuration');
    }

    /**
     * A Configuration Event belongs to a Configuration
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function generalConfiguration(){
        return $this->belongsTo('App\GeneralConfig');
    }
}
