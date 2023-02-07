<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Mistakes extends Model
{
    /**/
    protected $table = 'mistakes';

    public function question()
    {
        return $this->belongsTo('App\Question');
    }

    public function users()
    {
        return $this->belongsTo('App\User');
    }
   
}