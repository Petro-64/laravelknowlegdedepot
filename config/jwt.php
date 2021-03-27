<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Secret to use with $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
    |--------------------------------------------------------------------------
    |
    | good example is in the https://developer.okta.com/blog/2019/02/04/create-and-verify-jwts-in-php
    | 'validityTimeout' is how lonmg in seconds this token remains valid
    |
    */
       'secret' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9',
       'validityTimeout' => 86400
    ];
