<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response as FacadeResponse;
use App\Subject;
use App\Question;
use App\QuestionContribution;
use App\QuestionReport;
use App\Answer;
use App\Comment;
use App\AnswerContribution;
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
use App\Jobs\SendEmailJob;
use App\Jobs\SendPasswordResetEmail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cookie;
use App\Mistakes;

class AngularController extends Controller
{

    private $memcached;
    private $adminMemcachedModels;

    function __construct(TestResult $testResult) {
        $this->memcache = new Memcached;
        $this->adminMemcachedModels = new AdminMemcachedModels;
        $this->userMemcachedModels = new UserMemcachedModels;
        $this->memcache->addServer('localhost', 11211);
        $this->testResult = $testResult;
    }

    public function index() {
        return view('spa');
    }

    public function getsubjectsuser(){
        //$subjects = Subject::where('active', 1)->where('questions_number', '!=', 0)->orderBy('created_at', 'asc')->get(); 
 
        $subjects = DB::table('subjects')
        ->leftjoin('questions', 'questions.subject_id', '=', 'subjects.id')
        ->select(DB::raw('subjects.id as id, subjects.name as name, subjects.active as active, subjects.created_at as created_at, subjects.updated_at as updated_at, COUNT(subjects.name) as questions_number'))
        ->groupBy(DB::raw("subjects.id"))
        ->where('questions.active', '=', 1)
        ->where('subjects.active', '=', 1)
        ->get();

        //$this->memcache->set(self::subjectsUserKey, $subjects, self::memcachedTimeout);
        return response()->json(['subjects'=>$subjects]);
        //return $this->userMemcachedModels->getSubjectsUser();
    }

    public function login(Request $request){
        $thisUser = User::where('email', $request->email)->first();
        if($thisUser && Hash::check($request->password, $thisUser->password)){
            $roleId = DB::table('role_user')->where('user_id', $thisUser->id)->pluck('role_id')->first();
            $header = json_encode(['typ' => 'JWT','alg' => 'HS256']);
            $payload = json_encode(['user_id' =>$thisUser->id,'role_id' => $roleId, 'login_timestamp' => time()]);
            $base64UrlHeader = base64_encode($header);
            $base64UrlPayload = base64_encode($payload);
            $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, Config::get('jwt.secret'), true);
            $base64UrlSignature = base64_encode($signature);
            $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
            return response()->json(['success'=>'true', 'id'=>$thisUser->id, 'name'=>$thisUser->name, 'suspension_reason'=>$thisUser->suspension_reason, 'role_id'=>$roleId, 'cookie_consent_given'=>$thisUser->cooklie_consent_given, 'jwt_token'=>$jwt]);
        } else {
            //return response()->json(['data'=>['success'=>'false', 'message'=>'Wrong email or password']]);
            return response()->json(      [                 'api_status' => '401',                'message' => 'UnAuthenticated',          ], 401);
        }
    }

    public function mysqldump(){
        //$command = "mysqldump travel_list > /home/petro/logs/dump.sql";
        /*
        $file = '/home/peter/blog/storage/logs/my.txt';
        $current = file_get_contents($file);
        $current .= $e->getMessage()."\n";
        file_put_contents($file, $current);

        finally done manually

        ssh into srever; ssh rabbit@138.197.132.107

        pw: B11

        cd /home/rabbit

        command: mysqldump -u root -p travel_list > dump.sql

        pw: same;

        dump is updated....

        go back to VBox

        execute this: scp rabbit@138.197.132.107:/home/rabbit/dump.sql /home/petro/Desktop/dump/dump.sql

        or other file name....

        done 17.03.23

        */
        //DB::statement($command);
        $contents = "Hello11";
        $response = FacadeResponse::make($contents, 200);
        return $response;
    }

    public function getresults(Request $request){
        $parseResult = ParseJWToken::doParse($request->header('JWToken'));/// we need this to retrieve user id
        $this->testResult->removeAllEmptyResults();
        $testingResults = DB::table('testing_results')
        ->join('subjects', 'testing_results.subject_id', '=', 'subjects.id')
        ->select(DB::raw('testing_results.id as resultId, testing_results.answered_questions_number, IF(testing_results.answered_questions_number=0, 0, testing_results.correct_questions_number/testing_results.answered_questions_number) as quality, 
        testing_results.created_at as createdAt, subjects.name as subjectName, subjects.id as subjectId'))
        ->where('testing_results.user_id', '=', $parseResult['user_id'])
        ->where('subjects.active', '=', 1)
        ->orderBy('testing_results.created_at', 'asc')
        ->get();
        //$this->memcache->set($resultsKeyName, $testingResults, self::memcachedTimeout);
        return response()->json(['results' => $testingResults]);
        //return $this->userMemcachedModels->getResults($request);
    }
}