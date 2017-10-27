<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MethodGroupTranslation extends Model
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['method_group_id', 'language_code', 'name', 'description'];

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
     * An Method Group Translation has one Method Group
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function methodGroup(){
        return $this->belongsTo('App\MethodGroup');
    }
}
