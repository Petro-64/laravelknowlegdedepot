<?php

namespace App\MyLibs;

use App\Subject;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Memcached;
use App\MyLibs\ParseJWToken;

class AdminMemcachedModels {

    private $memcached;
    const memcachedPort = 11211;
    const memcachedServer = 'localhost';
    const subjectsKey = 'subjects';
    const usersKey = 'users';
    const memcachedTimeout = 6000;

    function __construct() {
        $this->memcache = new Memcached;
        $this->memcache->addServer(self::memcachedServer, self::memcachedPort);
    }

    public function getSubjectsAdmin(){
        //subjects cache is being cleared in app\Observers\SubjectObserver.php
        if(false) {//$this->memcache->get(self::subjectsKey) != null
            return response()->json(['payload'=>['success'=>'true', 'subjects'=>$this->memcache->get(self::subjectsKey)]]);
        } else {
            $subjects = Subject::orderBy('created_at', 'asc')->get(); 
            $this->memcache->set(self::subjectsKey, $subjects, self::memcachedTimeout);
            return response()->json(['payload'=>['success'=>'true', 'subjects'=>$subjects]]);
        }
    }

    public function getUsers(){
        //users cache is being cleared in app\Observers\UserObserver.php
        if(false) {//$this->memcache->get(self::usersKey) != null
            return response()->json(['payload'=>['success'=>'true', 'users'=>$this->memcache->get(self::usersKey)]]);
        } else {
            $users = User::orderBy('created_at', 'asc')->get();
            $this->memcache->set(self::usersKey, $users, self::memcachedTimeout);
            return response()->json(['payload'=>['success'=>'true', 'users'=>$this->memcache->get(self::usersKey)]]);
        }
    }
}