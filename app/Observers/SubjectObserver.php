<?php

namespace App\Observers;
use Memcached;
use App\Subject;

class SubjectObserver
{
    private $memcached;
    const memcachedPort = 11211;
    const memcachedServer = 'localhost';
    const subjectsKey = 'subjects';
    const subjectsUserKey = 'subjectsUser';
    const memcachedTimeout = 6000;

    function __construct() {
        $this->memcache = new Memcached;
        $this->memcache->addServer(self::memcachedServer, self::memcachedPort);
    }

    private function clearAllSubjects() {
        $this->memcache->delete(self::subjectsKey);
        $this->memcache->delete(self::subjectsUserKey);
    }

    public function retrieved(Subject $subject) {
        /**/
    }

    public function created(Subject $subject) {
        $this->clearAllSubjects();
    }

    public function deleted(Subject $subject) {
        $this->clearAllSubjects();
    }

    public function updated(Subject $subject) {
        $this->clearAllSubjects();
    }
}