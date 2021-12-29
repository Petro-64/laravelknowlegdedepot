<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Subject;
use App\Question;
use App\QuestionContribution;
use App\Answer;
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


class ReactController extends Controller
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
        $subjects = Subject::where('active', 1)->where('questions_number', '!=', 0)->orderBy('created_at', 'asc')->get(); 
        //$this->memcache->set(self::subjectsUserKey, $subjects, self::memcachedTimeout);
        return response()->json(['payload'=>['success'=>'true', 'subjects'=>$subjects]]);
        //return $this->userMemcachedModels->getSubjectsUser();
    }

    public function getsubjectsadmin(){
        $subjects = Subject::orderBy('created_at', 'asc')->get(); 
        //$this->memcache->set(self::subjectsKey, $subjects, self::memcachedTimeout);
        return response()->json(['payload'=>['success'=>'true', 'subjects'=>$subjects]]);
        //return $this->adminMemcachedModels->getSubjectsAdmin();
    }

    public function getresults(Request $request){
        $parseResult = ParseJWToken::doParse($request->header('JWToken'));/// we need this to retrieve user id
        $testingResults = DB::table('testing_results')
        ->join('subjects', 'testing_results.subject_id', '=', 'subjects.id')
        ->select(DB::raw('testing_results.id as resultId, testing_results.answered_questions_number, IF(testing_results.answered_questions_number=0, 0, testing_results.correct_questions_number/testing_results.answered_questions_number) as quality, 
        testing_results.created_at as createdAt, subjects.name as subjectName, subjects.id as subjectId'))
        ->where('testing_results.user_id', '=', $parseResult['user_id'])
        ->where('subjects.active', '=', 1)
        ->orderBy('testing_results.created_at', 'asc')
        ->get();
        //$this->memcache->set($resultsKeyName, $testingResults, self::memcachedTimeout);
        return response()->json(['payload'=>['success'=>'true', 'results' => $testingResults]]);
        //return $this->userMemcachedModels->getResults($request);
    }

    public function changepassword(Request $request){
        $parseResult = ParseJWToken::doParse($request->header('JWToken'));/// we need this to retrieve user id
        $thisUser = User::where('id', $parseResult['user_id'])->first();
        $thisUser->password = Hash::make($request->password);
        if($thisUser->save()){
            return response()->json(['payload'=>['success'=>'true']]);
        } else {
            return response()->json(['payload'=>['success'=>'false']]);
        };
    }

    public function users(){
        $users = DB::table('users')
        ->join('role_user', 'users.id', '=', 'role_user.user_id')
        ->join('roles', 'roles.id', '=', 'role_user.role_id')
        ->leftjoin('testing_results', 'testing_results.user_id', '=', 'users.id')
        ->select(DB::raw('users.name as name, users.email as email, users.created_at as createdAt, users.id as user_id, roles.name as status, COUNT(testing_results.user_id) as resultsNumber, users.suspension_reason as suspension_reason'))
        ->groupBy(DB::raw("users.id, roles.name"))
        ->where('role_user.role_id', '!=', 2)
        ->orderBy('users.name', 'asc')
        ->get();
        return response()->json(['payload'=>['success'=>'true', 'users'=>$users]]);
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
            return response()->json(['data'=>['success'=>'true', 'id'=>$thisUser->id, 'name'=>$thisUser->name, 'suspension_reason'=>$thisUser->suspension_reason, 'role_id'=>$roleId, 'cookie_consent_given'=>$thisUser->cooklie_consent_given, 'jwt_token'=>$jwt]]);
        } else {
            return response()->json(['data'=>['success'=>'false', 'message'=>'Wrong email or password']]);
        }
    }

    public function startTesting(Request $request){
        if($request->testingSessionId == ''){
            $hashedTestingSession = Str::random();
            $sessId = $this->testResult->createTestingSession($hashedTestingSession);
            $givenSubjectAllQuestions = Question::where('subject_id', '=', $request->currentSubjectId)->where('active', '=', 1)->where('approved', '=', 1)->get();
            foreach ($givenSubjectAllQuestions as $value) {
                $this->testResult->createTemporaryQuestions($sessId, $value->id);
            }
            $token = $request->header('JWToken');
            $parseResult = ParseJWToken::doParse($token);
            $userId = $parseResult['user_id'];
            $this->testResult->createTestingResultItemEmpty($userId, $request->currentSubjectId, $sessId);
            return response()->json(['payload'=>['success'=>'true', 'testingSessionHash'=>$hashedTestingSession]]);
        } else {
            return response()->json(['payload'=>['success'=>'false', 'message'=>'Wrong request']]);
        }
    }
    
    public function processTesting(Request $request){
        if($request->ifToDestroyTemporaryQuestions == 1){//when user leaves Test page, we need to destroy temporary testing questions to prevent fraud
            $testingSessionId = TestingSession::where('session_hash', $request->testingSessionHash)->pluck('id')->first();
            $res = TemporaryTestingQuestions::where('session_id', $testingSessionId)->delete();
            return response()->json(['payload'=>['success'=>'true', 'message'=>'Temporary Questions has been destroyed']]);
        }


        if($request->answerId != null){//it means that we came here with answer
            $correct = Answer::where('id', $request->answerId)->first()->correct;
            if($request->testingSessionHash == null){
                return response()->json(['payload'=>['success'=>'false', 'message'=>'Testing session is unknown']]);
            };
            $testingSessionId = TestingSession::where('session_hash', $request->testingSessionHash)->pluck('id')->first();
            $this->testResult->updateTestingResultItem($testingSessionId, $correct);
            $this->testResult->updateTime($testingSessionId);//to be able to remove temporary testing questions and testing sessions, we need to update "updated at" field
        }
        $testingSessionId = TestingSession::where('session_hash', $request->testingSessionHash)->pluck('id')->first();
        $questionToShow = '';
        $collection = TemporaryTestingQuestions::where('answered', 0)->where('session_id', $testingSessionId)->pluck('id');
        $currentState = $this->testResult->getCurrentState($testingSessionId);
        if (!$collection->isEmpty()) {
            $ifRemainQuestions = 1; 
            $random = $collection->random(1);
            $quest = TemporaryTestingQuestions::find($random[0]);
            $currentQuestionId = $quest->question_id;
            $questionToShow = Question::where('id', $currentQuestionId)->pluck('name')->first();
            $answersToShow = Answer::select('name','id')->where('question_id', $currentQuestionId)->get()->toArray();
            $correctAnswerId = Answer::select('id')->where('question_id', $currentQuestionId)->where('correct', 1)->first()->id;
            $correctAnswerId = (int)$correctAnswerId;
            $correctAnswerId = (($correctAnswerId * 36) + 456) * 2;
            shuffle($answersToShow);
            $quest->answered = 1;
            $quest->save();
        } else {
            $res = TemporaryTestingQuestions::where('session_id', $testingSessionId)->delete();
            $answersToShow = [];
            $ifRemainQuestions = 0; 
            $currentQuestionId = null;
            $correctAnswerId = null;
        }
        return response()->json(['payload'=>['success'=>'true', 
                        'question'=>$questionToShow, 
                        'answersToShow'=>$answersToShow,
                        'ifRemainQuestions'=>$ifRemainQuestions,
                        'answered' => $currentState['answered'],
                        'correct' => $currentState['correct'],
                        'correctAnswerId' => $correctAnswerId
                    ]]);
    }

    public function editsubjects(Request $request ){
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:25',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['payload'=>['success'=>'false', 'message'=>'Subject field is required']]);
        }
        $subject = Subject::find($request->id);
        $subject->name = $request->name;
        $subject->save();
        return response()->json(['payload'=>['success'=>'true']]);
    }

    public function deletesubjects($id){
        $subject = Subject::find($id);
        if($subject->questions_number > 0){
            return response()->json(['payload'=>['success'=>'false', 'message'=>'Can\'t delete this subject, already questions added to it']]);
        }
        Subject::findOrFail($id)->delete();
        return response()->json(['payload'=>['success'=>'true']]);
    }

    public function deleteusers($id){
        $resultsNumber = TestingResults::where('user_id', $id)->pluck('id');
        if (!$resultsNumber->isEmpty()) {
            return response()->json(['payload'=>['success'=>'false',  'message'=>'Can\'t delete this user, he has some testing results']]);
        }

        $userRole =  DB::table('role_user')->select(DB::raw('role_user.role_id'))->where('role_user.user_id', '=', $id)->pluck('role_id');
        if ($userRole[0] == 1 || $userRole[0] == 2) {
            return response()->json(['payload'=>['success'=>'false',  'message'=>'Can\'t delete confirmed usere or admin']]);//
        }
        DB::table('role_user')->where('role_user.user_id', '=', $id)->delete();
        User::findOrFail($id)->delete();
        return response()->json(['payload'=>['success'=>'true']]);
    }

    public function getglobalsettings(){
        $emailConfirmation = Settings::where('id', 1)->pluck('email_confirmation')->first();
        $signupRecaptcha = Settings::where('id', 1)->pluck('signup_recaptcha')->first();
        return response()->json(['payload'=>['success'=>'true', 'settings'=>['emailConfirmation'=>$emailConfirmation, 'signupRecaptcha'=>$signupRecaptcha]]]);
    }

    public function toggleemailconfirmation(){
        $emailConfirmation = Settings::where('id', 1)->pluck('email_confirmation')->first();
        if($emailConfirmation == 0){
            $emailConfirmationToChange = 1;
        } else {
            $emailConfirmationToChange = 0;
        }
        $settings = Settings::find(1);
        $settings->email_confirmation = $emailConfirmationToChange;
        $settings->save();
        $signupRecaptcha = Settings::where('id', 1)->pluck('signup_recaptcha')->first();
        return response()->json(['payload'=>['success'=>'true', 'settings'=>['emailConfirmation'=>$emailConfirmationToChange, 'signupRecaptcha'=>$signupRecaptcha]]]);
    }

    public function togglerecaptcha(){
        $togglerecaptcha = Settings::where('id', 1)->pluck('signup_recaptcha')->first();
        if($togglerecaptcha == 0){
            $togglerecaptchaToChange = 1;
        } else {
            $togglerecaptchaToChange = 0;
        }
        $settings = Settings::find(1);
        $settings->signup_recaptcha = $togglerecaptchaToChange;
        $settings->save();
        $emailConfirmation = Settings::where('id', 1)->pluck('email_confirmation')->first();
        return response()->json(['payload'=>['success'=>'true', 'settings'=>['emailConfirmation'=>$emailConfirmation, 'signupRecaptcha'=>$togglerecaptchaToChange]]]);
    }

    public function toggleusersuspended($id, $reasonSuspension="none"){
        $user = User::where('id', $id)->pluck('id');
        if (!$user->isEmpty()) {
            $roleUser = Role_user::find($id);
            $userToChange = User::find($id);
            $currentRole = $roleUser['role_id'] == 1 ? 4 : 1;
            $roleUser->role_id = $currentRole;
            $userToChange->suspension_reason = $reasonSuspension;
            if($roleUser->save() && $userToChange->save()){
                return response()->json(['payload'=>['success'=>'true']]);
            };
            
        }
        return response()->json(['payload'=>['success'=>'true',  'message'=>'Can\'t find this user, check his id']]);
    }

    public function addsubjects(Request $request){
        $validator = Validator::make($request->all(), [
            'subject' => 'required|max:25',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['payload'=>['success'=>'false', 'message'=>'Subject field is required']]);
        }
        
        $subject = new Subject;
        $subject->name = $request->subject;
        $subject->active = 1;
        $subject->questions_number = 0;
        $subject->save();
        return response()->json(['payload'=>['success'=>'true']]);
    }

    public function addmycontribution(Request $request) {
        $validator = Validator::make($request->all(), [
            'question' => 'required|max:1000',
            'firstAnswer' => 'required|max:1000',
            'secondAnswer' => 'required|max:1000',
            'thirdAnswer' => 'required|max:1000',
            'fourthAnswer' => 'required|max:1000',
            'subjectId' => 'required',
        ]);
               
        if ($validator->fails()) {
            return response()->json(['payload'=>['success'=>'false',  'message'=>'Check data you send']]);
        }

        $parseResult = ParseJWToken::doParse($request->header('JWToken'));/// we need this to retrieve user id

        $question = new QuestionContribution;
        $question->subject_id = $request->subjectId;
        $question->user_id = $parseResult['user_id'];
        $question->name = $request->question;
        $question->active = 0;
        $question->approved = 0;
        $savingSuccess = $question->save();
        $questionId = $question->id;
        if($savingSuccess){
            $answersArray = array($request->firstAnswer, $request->secondAnswer, $request->thirdAnswer, $request->fourthAnswer);
            foreach ($answersArray as $key => $value) {
                $answer = new AnswerContribution;
                $answer->question_id = $questionId;
                $answer->name = $value;
                $answer->active = 1;
                if($key < 3){
                    $answer->correct = 0;
                } else {
                    $answer->correct = 1;
                }
                $answer->save();
            }
        }
        return response()->json(['payload'=>['success'=>'true']]);
    }

    public function questions($id = 1, $status = 1){
        switch ( $status ) {
            case 1:
                $rawString = 'questions.active IN (0,1)';
                break;
            case 2:
                $rawString = 'questions.active IN (1)';
                break;
            case 3:
                $rawString = 'questions.active IN (0)';
                break;
            default:
                $rawString = 'questions.active IN (0,1)';
        }

        $questions = DB::table('questions')
        ->select(DB::raw('questions.id as id, questions.name as name, questions.active as active, questions.created_at as created_at'))
        ->where('questions.subject_id', '=', $id)
        ->whereRaw($rawString)
        ->orderBy('questions.id', 'asc')
        ->get();
        return response()->json(['payload'=>['success'=>'true', 'subjectId'=>$id, 'questions'=>$questions]]);
    }

    public function answers($id){
        if(!isset($id)){
            return response()->json(['payload'=>['success'=>'false', 'message'=>'Question id is required and is numeric']]);
        }

        $answersToShow = Answer::select('name','id', 'correct')->where('question_id', $id)->orderBy('correct', 'asc')->get()->toArray();

        return response()->json(['payload'=>['success'=>'true', 'answers' => $answersToShow]]);
    }

    public function togglesubjectactivity($id){
        $subject = Subject::find($id);
        if($subject->active == 0){
            $active = 1;
        } else {
            $active = 0;
        }
        $subject->active = $active;
        $subject->save();
        return response()->json(['payload'=>['success'=>'true']]);
    }

    public function togglequestionactivity($id){
        if(!isset($id)){
            return response()->json(['payload'=>['success'=>'false', 'message'=>'Question id is required and is numeric']]);
        }

        $question = Question::find($id);
        if(!isset($question)){
            return response()->json(['payload'=>['success'=>'false', 'message'=>'Cant find question by this id']]);
        }

        if($question->active == 0){
            $active = 1;
        } else {
            $active = 0;
        }

        $question->active = $active;
        $question->save();

        return response()->json(['payload'=>['success'=>'true', 'questionId'=>$id]]);
    }

    public function addquestion(Request $request){
        $validator = Validator::make($request->all(), [
            'subjectId' => 'required|max:4',
            'question' => 'required|max:1000',
            'firstAnswer' => 'required|max:1000',
            'secondAnswer' => 'required|max:1000',
            'thirdAnswer' => 'required|max:1000',
            'fourthAnswer' => 'required|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['payload'=>['success'=>'false', 'message'=>'Questions and answers are is required']]);
        }
        
        DB::beginTransaction();

        $question = new Question;
        $question->subject_id = $request->subjectId;  
        $token = $request->header('JWToken');
        $parseResult = ParseJWToken::doParse($token);
        $question->user_id = $parseResult['user_id'];
        $question->name = $request->question;
        $question->active = 1;
        $question->approved = 1;
        $savingSuccess = $question->save();
        $questionId = $question->id;
        /////////////////////////// fourth question is always correct ////////////////////////////
        if($savingSuccess){
            $answersArray = array($request->firstAnswer, $request->secondAnswer, $request->thirdAnswer, $request->fourthAnswer);
            foreach ($answersArray as $key => $value) {
                $answer = new Answer;
                $answer->question_id = $questionId;
                $answer->name = $value;
                $answer->active = 1;
                if($key < 3){
                    $answer->correct = 0;
                } else {
                    $answer->correct = 1;
                }
                $answer->save();
            }
        }
        
        // to update subjects table
        $subjects = Subject::orderBy('created_at', 'asc')->get();
        foreach ($subjects as $subject) {
            $subjId = $subject->id;
            $count = Question::where('subject_id','=', $subjId)->count();

            $subject = Subject::find($subjId);
            $subject->questions_number = $count;
            $subject->save();
        }

        DB::commit();// transaction ended

        return response()->json(['payload'=>['success'=>'true']]);
    }

    public function toggleuserconfirm($id){
        $user = User::where('id', $id)->pluck('id');
        if (!$user->isEmpty()) {
            $roleUser = Role_user::find($id);
            $roleUser->role_id = 1;
            $roleUser->save();
            return response()->json(['payload'=>['success'=>'true']]);
        }
        return response()->json(['payload'=>['success'=>'false',  'message'=>'Can\'t find this user, check his id']]);
    }

    function cookieconsent($id){
        if(!isset($id)){
            return response()->json(['payload'=>['success'=>'false', 'message'=> 'User Id missing']]);
        }
        $user = User::where('id', $id)->first();
        if ($user) {
            $user->cooklie_consent_given = 1;
            if($user->save()){
                return response()->json(['payload'=>['success'=>'true']]);
            };
        }
        return response()->json(['payload'=>['success'=>'false',  'message'=>'Can\'t find this user, check id']]);
    }
    
    public function resendemailconfirmation($id){
        if(!isset($id)){
            return response()->json(['payload'=>['success'=>'false', 'message'=> 'User Id missing']]);
        }
        $user = User::find($id);
        if(dispatch(new SendEmailJob($user->name, $user->confirm_hash, $user->email))){
            return response()->json(['payload'=>['success'=>'true']]);
        };
    }

    public function forgotpassword(Request $request){
        $projectURL = Config::get('global.projectUrl');
        $expirationTimeInHours = (Config::get('passwordreset.validityTimeout')/24)/3600;
        $message='';
        if($request->email == '' && $request->username == ''){
            $message = 'email and username are empty';
            return response()->json(['data'=>['success'=>'false', 'message'=>$message]]);//Lg6uJ6b
        };
        $userByEmail = User::where('email', $request->email)->first();
        if($userByEmail){/// first priority is valid email, if it's provided, we use it
            $payload = json_encode(['user_id' =>$userByEmail->id,'role_id' => 3, 'login_timestamp' => time()]);
            $base64UrlPayload = base64_encode($payload);
            $jwt = $base64UrlPayload;
            $userByEmail->passwresethash = $jwt;
            if($userByEmail->save()){
                ////////////////////////////mail will be sent here///////////////////////////////
                dispatch(new SendPasswordResetEmail($userByEmail->name, $jwt, $userByEmail->email, $projectURL, $expirationTimeInHours));
                return response()->json(['data'=>['success'=>'true']]);
            };
        } else {/// if no valid email provided, we'll try to use username
            $userByName = User::where('name', $request->username)->first();
            if($userByName){
                $payload = json_encode(['user_id' =>$userByName->id,'role_id' => 3, 'login_timestamp' => time()]);
                $base64UrlPayload = base64_encode($payload);
                $jwt = $base64UrlPayload;
                $userByName->passwresethash = $jwt;
                if($userByName->save()){
                /////////////////////////////////mail will be sent here////////////////////////////
                    dispatch(new SendPasswordResetEmail($userByName->name, $jwt, $userByName->email, $projectURL, $expirationTimeInHours));
                    return response()->json(['data'=>['success'=>'true']]);
                };
            }//we will not neither deny or confirm and not to expose valid emails usernames
            return response()->json(['data'=>['success'=>'true']]);//anyways we return true even there is no such a user not to allow hacker to query and try to disclose valid names or emails
        }
        
    }

    public function resetpassword(Request $request){
        if($request->password == '' ||  $request->passwordRepeat == ''){
            $message = 'password not provided';
            return response()->json(['data'=>['success'=>'false', 'message'=>$message]]);
        };

        if($request->password != $request->passwordRepeat){
            $message = 'passwords are not matching';
            return response()->json(['data'=>['success'=>'false', 'message'=>$message]]);
        };

        $passwordreserhash = Cookie::get('passwordReset');

        if($passwordreserhash == ''){
            $message = 'Network error, try again later';
            return response()->json(['data'=>['success'=>'false', 'message'=>$message]]);
        };

        $delta = time() - json_decode(base64_decode($passwordreserhash))->{'login_timestamp'};

        if($delta > Config::get('passwordreset.validityTimeout')){
            return response()->json(['data'=>['success'=>'false', 'message'=>'token expired']]);// check token expiration
        }

        $userByPasswordHash = User::where('passwresethash', $passwordreserhash)->first();
        if($userByPasswordHash){
            $userByPasswordHash->password = Hash::make($request->password);
            $userByPasswordHash->passwresethash = '';
            if($userByPasswordHash->save()){
                return response()->json(['data'=>['success'=>'true']]);
            };
        };

        return response()->json(['data'=>['success'=>'false', 'message'=>'Network error, try again later']]);
    }

    public function signup(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['data'=>['success'=>'false', 'message'=>'Name and password fields are mandatory']]);
        }

        $validator1 = Validator::make($request->all(), [
            'email' => 'email:filter'
        ]);

        if ($validator1->fails()) {
            return response()->json(['data'=>['success'=>'false', 'message'=>'Please check email address']]);
        }

        $emailUnique = true;
        $userNameIsUnique = true;
        $users = User::all();
        foreach ($users as $user) {
            if( $request->email == $user->email){
                $emailUnique = false;
                break;
            };
            if( $request->name == $user->name){
                $userNameIsUnique = false;
                break;
            };
        }

        if (!$emailUnique == true) {
            return response()->json(['data'=>['success'=>'false', 'message'=>'This email is already taken']]);
        }

        if (!$userNameIsUnique == true) {
            return response()->json(['data'=>['success'=>'false', 'message'=>'This name is already taken']]);
        }

        $data = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'confirm_hash' => Str::random(32),
        ]);

        if(Settings::where('id', 1)->pluck('email_confirmation')->first() == 1) {
            $defaultRoleId = 3;// need email confirmation
        } else  {
            $defaultRoleId = 1;// dont need email confirmation
        }

        DB::insert('insert into role_user (role_id, user_id) values (?, ?)', [$defaultRoleId, $data->id]);
        $header = json_encode(['typ' => 'JWT','alg' => 'HS256']);
        $payload = json_encode(['user_id' =>$data->id,'role_id' => $defaultRoleId, 'login_timestamp' => time()]);
        $base64UrlHeader = base64_encode($header);
        $base64UrlPayload = base64_encode($payload);
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, Config::get('jwt.secret'), true);
        $base64UrlSignature = base64_encode($signature);
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
        $projectURL = Config::get('global.projectUrl');
        if(Settings::where('id', 1)->pluck('email_confirmation')->first() == 1) {
            dispatch(new SendEmailJob($request->name, $data->confirm_hash, $request->email, $projectURL));
        }

        return response()->json(['data'=>['success'=>'true', 'id'=>$data->id, 'name'=>$request->name, 'role_id'=>$defaultRoleId, 'cookie_consent_given'=>0, 'jwt_token'=>$jwt]]);
    }

    public function getcontributionadmin(){/// gets all contribution of all user no filter
        $contribution = DB::table('questions_contribution')
        ->join('users', 'questions_contribution.user_id', '=', 'users.id')
        ->join('subjects', 'questions_contribution.subject_id', '=', 'subjects.id')
        ->select(DB::raw('questions_contribution.id as resultId, questions_contribution.approved as status,  
        questions_contribution.created_at as createdAt, subjects.name as subjectName, users.name as userName'))
        ->orderBy('questions_contribution.created_at', 'asc')
        ->get();
        return response()->json(['payload'=>['success'=>'true', 'contibution' => $contribution]]);
    }

    public function getcontributionuser(Request $request){
        $parseResult = ParseJWToken::doParse($request->header('JWToken'));/// we need this to retrieve user id
        $contribution = DB::table('questions_contribution')
        ->join('subjects', 'questions_contribution.subject_id', '=', 'subjects.id')
        ->select(DB::raw('questions_contribution.id as resultId, questions_contribution.approved as status,  
        questions_contribution.created_at as createdAt, subjects.name as subjectName'))
        ->where('questions_contribution.user_id', '=', $parseResult['user_id'])
        ->orderBy('questions_contribution.created_at', 'asc')
        ->get();
        return response()->json(['payload'=>['success'=>'true', 'contibution' => $contribution]]);
    }

    public function getcontributionitemadmin($id){
        if(!isset($id)){
            return response()->json(['payload'=>['success'=>'false', 'message'=> 'conribution Id missing']]);
        }
        $questionContribution = QuestionContribution::where('id', $id)->first();
        if(!$questionContribution){
            return response()->json(['payload'=>['success'=>'false', 'message'=> 'conribution Id is wrong']]);
        }
        $answersContribution = AnswerContribution::where('question_id', $id)->get();
        $question = $questionContribution->name;
        $subjectId = $questionContribution->subject_id;
        $userId = $questionContribution->user_id;
        $uncorrectIterator = 0;
        foreach ($answersContribution as $value) {
            if($value['correct'] == 0){
                $name = 'uncorrect'.$uncorrectIterator;
                $uncorrectIterator += 1;
                $$name = $value['name'];
            } else {
                $answerCorrect = $value['name'];// just comment
            };
        }
        return response()->json(['payload'=>['success'=>'true', 'content' => 
        ['question' => $question, 'subjectId' => $subjectId, 'userId' => $userId, 'contibutionid' => $id, 'answerCorrect' => $answerCorrect, 'uncorrect0' => $uncorrect0, 'uncorrect1' => $uncorrect1, 'uncorrect2' => $uncorrect2]]]);
    }

    public function getcontributionitemuser(Request $request, $id){
        if(!isset($id)){
            return response()->json(['payload'=>['success'=>'false', 'message'=> 'conribution Id missing']]);
        }
        $questionContribution = QuestionContribution::where('id', $id)->first();
        if(!$questionContribution){
            return response()->json(['payload'=>['success'=>'false', 'message'=> 'conribution Id is wrong']]);
        }

        $userIdfromContributionItem = $questionContribution->user_id;
        $parseResult = ParseJWToken::doParse($request->header('JWToken'));/// we need this to retrieve user id
        $userIdFromJWToken = $parseResult['user_id'];

        if($userIdfromContributionItem != $userIdFromJWToken){
            return response()->json(['payload'=>['success'=>'false', 'message'=> 'conribution Id is wrong']]);
        }

        //return response()->json(['payload'=>['success'=>'true', 'id1'=> $userIdfromContributionItem, 'id2'=> $userIdFromJWToken]]);



        $answersContribution = AnswerContribution::where('question_id', $id)->get();
        $question = $questionContribution->name;
        $subjectId = $questionContribution->subject_id;
        $userId = $questionContribution->user_id;
        $questionStatus = $questionContribution->approved;
        $createdAt = substr($questionContribution->created_at, 0, 11);
        $uncorrectIterator = 0;
        foreach ($answersContribution as $value) {
            if($value['correct'] == 0){
                $name = 'uncorrect'.$uncorrectIterator;
                $uncorrectIterator += 1;
                $$name = $value['name'];
            } else {
                $answerCorrect = $value['name'];
            };
        }
        return response()->json(['payload'=>['success'=>'true', 'content' => 
        ['question' => $question, 'subjectId' => $subjectId, 'userId' => $userId, 'contibutionid' => $id, 
          'answerCorrect' => $answerCorrect, 'uncorrect0' => $uncorrect0, 'uncorrect1' => $uncorrect1, 'uncorrect2' => $uncorrect2, 'questionStatus' => $questionStatus, 'createdAt' => $createdAt]]]);
    }


    public function approvecontributionitemadmin(Request $request){
        $validator = Validator::make($request->all(), [
            'question' => 'required|max:1000',
            'firstAnswer' => 'required|max:1000',
            'secondAnswer' => 'required|max:1000',
            'thirdAnswer' => 'required|max:1000',
            'fourthAnswer' => 'required|max:1000',
            'contibutionid' => 'required',
        ]);
               
        if ($validator->fails()) {
            return response()->json(['payload'=>['success'=>'false',  'message'=>'Check data you send']]);
        }

        $question = new Question;
        $question->subject_id = $request->subjectId;
        $question->name = $request->question;
        $question->active = 1;
        $question->approved = 1;
        $question->user_id = $request->userId;
        $savingSuccess = $question->save();
        $questionId = $question->id;
       
        if($savingSuccess){
            $answersArray = array($request->firstAnswer, $request->secondAnswer, $request->thirdAnswer, $request->fourthAnswer);
            foreach ($answersArray as $key => $value) {
                $answer = new Answer;
                $answer->question_id = $questionId;
                $answer->name = $value;
                $answer->active = 1;
                if($key < 3){
                    $answer->correct = 0;
                } else {
                    $answer->correct = 1;
                }
                $answer->save();
            }

            $userContribution = QuestionContribution::where('id', $request->contibutionid)->first();
            if ($userContribution) {
                $userContribution->approved = 1;
                if($userContribution->save()){
                    return response()->json(['payload'=>['success'=>'true']]);
                };
            }
        }

        return response()->json(['payload'=>['success'=>'false', 'message' => 'Network error, try again later']]);
    }

    public function declinecontributionitemadmin(Request $request){
        $validator = Validator::make($request->all(), [
            'contibutionid' => 'required',
        ]);
               
        if ($validator->fails()) {
            return response()->json(['payload'=>['success'=>'false',  'message'=>'Check data you send']]);// just to try
        }

        $userContribution = QuestionContribution::where('id', $request->contibutionid)->first();
        if ($userContribution) {
            $userContribution->approved = 2;
            if($userContribution->save()){
                return response()->json(['payload'=>['success'=>'true']]);
            };
        }
        return response()->json(['payload'=>['success'=>'false', 'message' => 'Network error, try again later']]);
    }
}