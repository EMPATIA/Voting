<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GeneralConfig extends Model
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['general_config_key', 'general_config_type_id','parameter_key', 'greater', 'equal', 'less', 'value'];

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
     * An General Config belongs to General Config Types
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function generalConfigType(){
        return $this->belongsTo('App\GeneralConfigType');
    }


    /**
     * An General Config has many Configuration Events
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function configurationEvents(){
        return $this->hasMany('App\ConfigurationEvent');
    }


}
