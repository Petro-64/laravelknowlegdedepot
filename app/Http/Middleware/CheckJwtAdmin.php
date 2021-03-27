<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Config;
use App\MyLibs\ParseJWToken;

class CheckJwtAdmin
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

        if($parseResult['signature_valid']  && ($parseResult['role_id'] == 2) && ($parseResult['delta'] < Config::get('jwt.validityTimeout'))){
            return $next($request);
        } else {
            return response()->json(['payload'=>['success'=>'false', 'message' => "unautorized access"]]);
        }
    }
}