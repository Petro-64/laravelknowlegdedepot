<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuestionContribution extends Model
{
    /**/
    protected $table = 'questions_contribution';

    public function subject()
    {
        return $this->belongsTo('App\Subject');
    }

    public function users()
    {
        return $this->belongsTo('App\User');
    }
   
}