<?php

namespace App\Observers;
use Memcached;
use App\TemporaryTestingQuestions;
use Illuminate\Support\Facades\DB;


class TemporaryTestingQuestionsObserver
{
    private $memcached;
    const memcachedPort = 11211;
    const memcachedServer = 'localhost';
    const memcachedTimeout = 6000;

    function __construct() {
        $this->memcache = new Memcached;
        $this->memcache->addServer(self::memcachedServer, self::memcachedPort);
    }

    public function retrieved(TemporaryTestingQuestions $temporaryTestingQuestions) {

    }

    public function created(TemporaryTestingQuestions $temporaryTestingQuestions) {
        DB::delete('delete from temporary_testing_questions where DATE(updated_at) < DATE_SUB(CURDATE(), INTERVAL 1 DAY)');
    }

    public function deleted(TemporaryTestingQuestions $temporaryTestingQuestions) {

    }

    public function updated(TemporaryTestingQuestions $temporaryTestingQuestions) {

    }
}