<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Config;
use App\MyLibs\ParseJWToken;
use App\Comment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class CheckQuestionsReportRateLimiter
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
        $timeout = Config::get('ratelimiter.questionReportRatelimiterHours');
        $reportsNumber = Config::get('ratelimiter.questionReportRatelimiterReports');
        $count = DB::table('questions_report')
        ->select(DB::raw('COUNT(*) as count'))
        ->where('questions_report.user_id', '=', ParseJWToken::doParse($request->header('JWToken'))['user_id'])
        ->where('created_at', '>',  Carbon::now()->subHours($timeout)->toDateTimeString())
        ->pluck('count');
        if($count[0] >= $reportsNumber){
            return response()->json(['payload'=>['success'=>'false', 'message' => "ratelimiter issue"]]);
        } else {
            return $next($request);
        }
    }
}