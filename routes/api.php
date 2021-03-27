<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Subject;
use App\Question;
use App\Answer;
use App\Http\Resources\Answer as AnswerResource;
use App\Http\Resources\AnswerCollection as AnswerToQuestionResource;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
