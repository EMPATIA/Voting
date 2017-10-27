<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Method extends Model
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['code', 'method_group_id'];

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
     * An Method belongs to Method Group
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function methodGroup(){
        return $this->belongsTo('App\MethodGroup');
    }


    /**
     * An Method has many Events
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function events(){
        return $this->hasMany('App\Event');
    }

    /**
     * An Method has many Configurations
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function configurations(){
        return $this->hasMany('App\Configuration');
    }

    /**
     * An Method has many Method Translations
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function methodTranslations(){
        return $this->hasMany('App\MethodTranslation');
    }

    /**
     * @param null $language
     * @return bool
     */
    public function translation($language = null)
    {
        $translation = $this->hasMany('App\MethodTranslation')->where('language_code', '=', $language)->get();
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
        $translations = $this->hasMany('App\MethodTranslation')->get();
        $this->setAttribute('translations',$translations);
        return $translations;
    }
}
