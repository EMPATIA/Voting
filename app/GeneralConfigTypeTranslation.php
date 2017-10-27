<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GeneralConfigTypeTranslation extends Model
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['general_config_type_id', 'language_code', 'name'];

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
     * An General Config Type Translation has one General Config Type
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function generalConfigType(){
        return $this->belongsTo('App\GeneralConfigType');
    }
}
