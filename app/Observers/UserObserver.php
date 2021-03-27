<?php

namespace App\Observers;
use Memcached;
use App\User;

class UserObserver
{
    private $memcached;
    const memcachedPort = 11211;
    const memcachedServer = 'localhost';
    const usersKey = 'users';
    const memcachedTimeout = 6000;

    function __construct() {
        $this->memcache = new Memcached;
        $this->memcache->addServer(self::memcachedServer, self::memcachedPort);
    }

    public function retrieved(User $subject) {
        // nothing to put here so  far....
    }

    public function created(User $user)
    {
        $this->memcache->delete(self::usersKey);
    }

    public function deleted(User $user)
    {
        $this->memcache->delete(self::usersKey);
    }

    public function updated(User $user)
    {
        $this->memcache->delete(self::usersKey);
    }
}