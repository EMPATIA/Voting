<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MethodGroup extends Model
{
    use SoftDeletes;

    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

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
     * An Method Group has many Methods
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function methods(){
        return $this->hasMany('App\Method');
    }

    /**
     * An Method Group has many Method Group Translations
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function methodGroupTranslations(){
        return $this->hasMany('App\MethodGroupTranslation');
    }

    /**
     * @param null $language
     * @return mixed
     */
    public function translation($language = null)
    {
        $translation = $this->hasMany('App\MethodGroupTranslation')->where('language_code', '=', $language)->get();
        if(sizeof($translation)>0){
            $this->setAttribute('name',$translation[0]->name);
            $this->setAttribute('description',$translation[0]->description);
            return true;
         } else {
            return false;
        }
    }

    public function newTranslation($language = null, $languageDefault = null) {
        $translation = $this->hasMany('App\MethodGroupTranslation')->orderByRaw("FIELD(language_code,'".$languageDefault."','".$language."')DESC")->first();
        $this->setAttribute('name',$translation->name ?? null);
    }


    /**
     * @return mixed
     */
    public function translations()
    {
        $translations = $this->hasMany('App\MethodGroupTranslation')->get();
        $this->setAttribute('translations',$translations);
        return $translations;
    }
}
