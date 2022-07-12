<?php

use Illuminate\Support\Str;

return [

    /*
   |
    | good example is in the https://developer.okta.com/blog/2019/02/04/create-and-verify-jwts-in-php
    |
    */
       'commentRatelimiterHours' => 24,
       'commentRatelimiterComments' => 3,
       'contributeRatelimiterHours' => 24,
       'contributeRatelimiterContributions' => 5,
       'questionReportRatelimiterHours' => 24,
       'questionReportRatelimiterReports' => 5
    ];