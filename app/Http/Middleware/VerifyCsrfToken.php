<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    //these are react api related URLs
    protected $except = [
        '/react/login',
        '/react/addsubjects',
        '/react/editsubjects',
        '/react/deletesubjects/*',
        '/react/deleteusers/*',
        '/react/deletequestion/*',
        '/react/signup',
        '/react/testing',
        '/react/startTesting',
        '/react/processTesting',
        '/react/changepassword',
        '/react/forgotpassword',
        '/react/resetpassword',
        '/react/addmycontribution',
        '/react/approvecontributionitemadmin',
        '/react/declinecontributionitemadmin',
        '/react/addquestion',
        '/react/questions/*',
        '/react/togglequestionactivity/*',
        '/react/answers/*',
        '/react/getquestionandanswerstoedit/*',
        '/react/editquestions',
        '/react/htmlentitiesconvertor3456346',
        '/react/removezeroansweredtestingresultsr',
    ];
}
