<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuestionReport extends Model
{
    /**/
    protected $table = 'questions_report';

    public function question()
    {
        return $this->belongsTo('App\Question');
    }

    public function users()
    {
        return $this->belongsTo('App\User');
    }
   
}