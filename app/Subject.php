<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\MemcachedModels;

class Subject extends MemcachedModels
{

    /**/
    public function questions()
    {
        return $this->hasMany('App\Question');
    }
    
}
