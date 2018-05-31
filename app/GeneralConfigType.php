<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GeneralConfigType extends Model
{
    use SoftDeletes;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['general_config_type_key','code'];

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
     * An General Config Types has many General Config
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function generalConfigs(){
        return $this->hasMany('App\GeneralConfig');
    }

    /**
     * An Method Group has many Method Group Translations
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function generalConfigTypeTranslations(){
        return $this->hasMany('App\GeneralConfigTypeTranslation');
    }

    /**
     * @param null $language
     * @return mixed
     */
    public function translation($language = null)
    {
        $translation = $this->hasMany('App\GeneralConfigTypeTranslation')->where('language_code', '=', $language)->get();
        if(sizeof($translation)>0){
            $this->setAttribute('name',$translation[0]->name);
            return true;
        } else {
            return false;
        }
    }

    public function newTranslation($language = null, $languageDefault = null) {
        $translation = $this->hasMany('App\GeneralConfigTypeTranslation')->orderByRaw("FIELD(language_code,'".$languageDefault."','".$language."')DESC")->first();
        $this->setAttribute('name',$translation->name ?? null);
    }


    /**
     * @return mixed
     */
    public function translations()
    {
        $translations = $this->hasMany('App\GeneralConfigTypeTranslation')->get();
        $this->setAttribute('translations',$translations);
        return $translations;
    }
}
