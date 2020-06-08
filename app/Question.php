<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    /**/
    public function subject()
    {
        return $this->belongsTo('App\Subject');
    }

    public function users()
    {
        return $this->belongsTo('App\User');
    }
   
}
