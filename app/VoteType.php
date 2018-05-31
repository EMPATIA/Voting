<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class VoteType extends Model
{
    use SoftDeletes;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['event_key','pos','weight'];

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
     * An Vote Types has many Vote
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function vote(){
        return $this->hasMany('App\Vote');
    }

    /**
     * An Method Group has many Method Group Translations
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function voteTypeTranslations(){
        return $this->hasMany('App\VoteTypeTranslation');
    }

    /**
     * @param null $language
     * @return mixed
     */
    public function translation($language = null)
    {
        $translation = $this->hasMany('App\VoteTypeTranslation')->where('lang_code', '=', $language)->get();
        if(sizeof($translation)>0){
            $this->setAttribute('text',$translation[0]->name);
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
        $translations = $this->hasMany('App\VoteTypeTranslation')->get();
        $this->setAttribute('translations',$translations);
        return $translations;
    }
}
