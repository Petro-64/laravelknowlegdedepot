<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class TemporaryTestingQuestionsModelAddServiceProvider extends ServiceProvider
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
        \App\TemporaryTestingQuestions::observe(\App\Observers\TemporaryTestingQuestionsObserver::class);
    }
}
