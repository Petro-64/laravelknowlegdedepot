<?php

namespace App\MyLibs;

use App\Subject;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Memcached;
use App\MyLibs\ParseJWToken;

class UserMemcachedModels {

    private $memcached;
    const memcachedPort = 11211;
    const memcachedServer = 'localhost';
    const subjectsUserKey = 'subjectsUser';
    const resultsKey = 'results';
    const memcachedTimeout = 6000;

    function __construct() {
        $this->memcache = new Memcached;
        $this->memcache->addServer(self::memcachedServer, self::memcachedPort);
    }

    public function getResults($request){
        $parseResult = ParseJWToken::doParse($request->header('JWToken'));/// we need this to retrieve user id
        $resultsKeyName = self::resultsKey.$parseResult['user_id'];
        if(false){//$this->memcache->get($resultsKeyName) != null //temporary disabled caching because react tests are not ready
            return response()->json(['payload'=>['success'=>'true', 'results'=>$this->memcache->get($resultsKeyName)]]);
        } else {
            $testingResults = DB::table('testing_results')
            ->join('subjects', 'testing_results.subject_id', '=', 'subjects.id')
            ->select(DB::raw('testing_results.id as resultId, testing_results.answered_questions_number, testing_results.correct_questions_number/testing_results.answered_questions_number as quality, 
            testing_results.created_at as createdAt, subjects.name as subjectName, subjects.id as subjectId'))
            ->where('testing_results.user_id', '=', $parseResult['user_id'])
            ->orderBy('testing_results.created_at', 'asc')
            ->get();
            $this->memcache->set($resultsKeyName, $testingResults, self::memcachedTimeout);
            return response()->json(['payload'=>['success'=>'true', 'results' => $testingResults]]);
        }
    }


    public function getSubjectsUser(){
        if(false) {//$this->memcache->get(self::subjectsUserKey) != null
            return response()->json(['payload'=>['success'=>'true', 'subjects'=>$this->memcache->get(self::subjectsUserKey)]]);
        } else {
            $subjects = Subject::where('active', 1)->orderBy('created_at', 'asc')->get(); 
            $this->memcache->set(self::subjectsUserKey, $subjects, self::memcachedTimeout);
            return response()->json(['payload'=>['success'=>'true', 'subjects'=>$subjects]]);
        }
    }
}