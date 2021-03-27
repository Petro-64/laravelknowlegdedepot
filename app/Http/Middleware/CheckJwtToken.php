<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Config;
use App\MyLibs\ParseJWToken;


class CheckJwtToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $jwt = $request->header('JWToken');
        $parseResult = ParseJWToken::doParse($jwt);
        //role_id == 3 is user with non-comfirmed email
        if($parseResult['signature_valid']  && (($parseResult['role_id'] == 1) || ($parseResult['role_id'] == 2) || ($parseResult['role_id'] == 3)) && ($parseResult['delta'] < Config::get('jwt.validityTimeout'))){
            return $next($request);
        } else {
            return response()->json(['payload'=>['success'=>'false', 'message' => "unautorized access"]]);
        }
    }
}