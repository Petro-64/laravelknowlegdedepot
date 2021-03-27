<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class SubjectModelAddServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        \App\Subject::observe(\App\Observers\SubjectObserver::class);
    }
}
