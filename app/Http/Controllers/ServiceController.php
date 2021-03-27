<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use App\Subject;
use App\Question;
use App\Answer;
use App\Settings;
use App\User;
use App\Role;
use App\Role_user;
use Illuminate\Support\Facades\Hash;
use App\MyLibs\JsonValidator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use App\MyLibs\AdminMemcachedModels;
use App\MyLibs\UserMemcachedModels;
use Illuminate\Support\Str;
use App\MyLibs\TestResult;
use Memcached;
use App\TestingSession;
use App\TemporaryTestingQuestions;
use App\TestingResults;
use App\MyLibs\ParseJWToken;
use Mail;

class ServiceController extends Controller
{
    public function verifyemail($token = null){
        $userId = User::where('confirm_hash', '=', $token)->pluck('id');
        if (count($userId) > 0) {
            Cookie::queue(cookie('needToRedirectToLogin', "val", 30, null, null, false, false));// here is actual value isn't important, this cookie indicates that we need to redirect to login page
            $roleUser = Role_user::where('user_id', '=', $userId)->first();
            $roleUser->role_id = 1;
            $roleUser->save();
            return redirect('/app');
        }
        return redirect('/');
    }

    public function passwordreset($token = null){
        if(strlen($token) > 0){
            $userByToken = User::where('passwresethash', '=', $token)->first();
            if($userByToken){
                Cookie::queue(cookie('passwordReset', $token, 86400, null, null, false, false));
                return redirect('/app');
            }
        }        
        return redirect('/');
    }
}