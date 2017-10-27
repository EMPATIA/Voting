<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Configuration extends Model
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['code', 'method_id', 'parameter_type'];

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
     * A Configuration has many Configuration Translations
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function configurationTranslations(){
        return $this->hasMany('App\ConfigurationTranslation');
    }

    /**
     * @param null $language
     * @return bool
     */
    public function translation($language = null)
    {
        $translation = $this->hasMany('App\ConfigurationTranslation')->where('language_code', '=', $language)->get();
        if(sizeof($translation)>0){
            $this->setAttribute('name',$translation[0]->name);
            $this->setAttribute('description',$translation[0]->description);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return mixed
     */
    public function translations()
    {
        $translations = $this->hasMany('App\ConfigurationTranslation')->get();
        $this->setAttribute('translations',$translations);
        return $translations;
    }

    /**
     * A Configuration has many Events
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function configurationEvents(){
        return $this->belongsToMany('App\ConfigurationEvent');
    }

    /**
     * A Configuration belongs to a Method
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function method(){
        return $this->belongsTo('App\Method');
    }


}
