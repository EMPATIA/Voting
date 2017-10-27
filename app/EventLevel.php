<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventLevel extends Model
{
      use SoftDeletes;

      protected $fillable = ['cb_key', 'event_id', 'value'];

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
       * A Event belongs to  Method
       *
       * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
       */
      public function events(){
          return $this->hasMany('App\Event');
      }
}
