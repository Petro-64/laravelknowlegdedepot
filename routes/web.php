<?php

use Illuminate\Support\Facades\Route;
use App\Subject;
use App\Question;
use App\Answer;
use Illuminate\Http\Request;
use App\Http\Resources\Answer as AnswerResource;
use App\Http\Resources\AnswerCollection as AnswerToQuestionResource;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();
Route::get('/corransw/{id}', 'TestsOneController@getCorrectAnswer');//need to have correct answer id to be able to show correcta answer after user clicked on answer
//Route::get('/', function () {  return view('home'); });

// hide old Laravel frontend start
Route::get('/', function () {    return redirect('/app');});
Route::get('/tests', function () {    return redirect('/app');});
Route::get('/tests/{id}', function () {    return redirect('/app');});
Route::get('/testing', function () {    return redirect('/app');});
Route::get('/testing/{id?}', function () {    return redirect('/app');});
Route::get('/home', function () {    return redirect('/app');});
Route::get('/contribution', function () {    return redirect('/app');});
// hide old Laravel frontend end

//Route::get('/contribution', function () { return view('contribution'); })->name('contribution');
//Route::get('/home', 'HomeController@index')->name('home');
//Route::get('/tests', 'TestsOneController@index')->name('tests');
//Route::get('/tests/{id}','TestsOneController@showsubject');
//Route::get('/testing','TestsOneController@testing');
//Route::get('/testing/{id?}','TestsOneController@testing');
Route::get('/verifyemailaddress/{token?}', 'ServiceController@verifyemail');
Route::get('/passwordreset/{token?}', 'ServiceController@passwordreset');

//react part of application starts
Route::get('/app','SpaController@index')->name('reacthome');
Route::get('/app/login','SpaController@index');
Route::get('/app/register','SpaController@index');
Route::get('/app/test','SpaController@index');
Route::get('/app/users','SpaController@index');
//react part of application ends

//react related public api starts
Route::get('/react/removezeroansweredtestingresults34563456sdfgs','ReactController@removezeroansweredtestingresults');//one time script temporary route to remove garbage reults when answered questions = zero
Route::get('/react/getglobalsettings','ReactController@getglobalsettings');
Route::get('/react/subjects','ReactController@getsubjectsuser');
Route::get('/react/ratelimiters','ReactController@getratelimiters');
Route::post('/react/login','ReactController@login');// needed to disable scrf token in app\Http\Middleware\VerifyCsrfToken.php
Route::post('/react/signup','ReactController@signup');
Route::post('/react/forgotpassword','ReactController@forgotpassword'); 
Route::post('react/startTesting','ReactController@startTesting');
Route::post('/react/processTesting','ReactController@processTesting'); 
Route::post('/react/resetpassword','ReactController@resetpassword');
Route::get('/react/htmlentitiesconvertor3456346','ReactController@htmlentitiesconvertor');// one time script to convert all &gt;.... shit into > or < ......
//react related user api starts
Route::group(['middleware' => ['ifJwTokenRoleExists']], function(){
    Route::get('/react/results','ReactController@getresults');
    Route::get('/react/emailconfirm/{id}','ReactController@getresults');
    Route::get('/react/cookieconsent/{id}','ReactController@cookieconsent');
    Route::post('/react/changepassword','ReactController@changepassword');
        Route::group(['middleware' => ['CheckContributionRateLimiter']], function(){
            Route::post('/react/addmycontribution','ReactController@addmycontribution');
        });
    Route::get('/react/getcontributionuser','ReactController@getcontributionuser');
    Route::get('/react/getcontributionitemuser/{id}','ReactController@getcontributionitemuser');
    Route::get('/react/resendemailconfirmation/{id}','ReactController@resendemailconfirmation');
        Route::group(['middleware' => ['CheckCommentsRateLimiter']], function(){
            Route::post('/react/addmycomment','ReactController@addmycomment');
        });

    //react related admin api starts
    Route::group(['middleware' => ['ifJwTokenAdmin']], function(){
        Route::get('/react/subjectsadmin','ReactController@getsubjectsadmin');
        Route::get('/react/users','ReactController@users'); 
        Route::get('/react/getcontributionadmin','ReactController@getcontributionadmin');
        Route::get('/react/getcontributionitemadmin/{id}','ReactController@getcontributionitemadmin');
        Route::post('/react/approvecontributionitemadmin','ReactController@approvecontributionitemadmin');
        Route::post('/react/declinecontributionitemadmin','ReactController@declinecontributionitemadmin');
        Route::get('/react/togglesubjectactivity/{id}','ReactController@togglesubjectactivity');
        Route::get('/react/questions/{id}/{status}','ReactController@questions');
        Route::get('/react/answers/{id}','ReactController@answers');
        Route::get('/react/getquestionandanswerstoedit/{id}','ReactController@getquestionandanswerstoedit');
        Route::get('/react/togglequestionactivity/{id}','ReactController@togglequestionactivity');
        Route::get('/react/toggleemailconfirmation','ReactController@toggleemailconfirmation'); 
        Route::get('/react/toggletogglerecaptcha','ReactController@togglerecaptcha'); 
        Route::get('/react/toggleuserconfirm/{id}','ReactController@toggleuserconfirm');
        Route::get('/react/toggleusersuspended/{id}/{reasonSuspension?}','ReactController@toggleusersuspended');
        Route::post('/react/editquestions','ReactController@editquestions');
        Route::post('/react/addsubjects','ReactController@addsubjects');
        Route::post('/react/editsubjects','ReactController@editsubjects');
        Route::post('/react/addquestion','ReactController@addquestion');
        Route::delete('/react/deletesubjects/{id}','ReactController@deletesubjects');
        Route::delete('/react/deleteusers/{id}','ReactController@deleteusers');
        Route::delete('/react/deletequestion/{id}','ReactController@deletequestion');
        ///!!! don't forget to excluse all react post delete put from scrf token protection here: app\Http\Middleware\VerifyCsrfToken.php
    });
}); 
//react related public api ends


Route::group(['middleware' => ['auth']], function(){
    Route::group(['middleware' => ['admin']], function(){
        Route::get('/subjects', function () { $subjects = Subject::orderBy('created_at', 'asc')->get();  return view('subjects', ['subjects' => $subjects]); })->name('subjects');
        Route::post('/subject', function (Request $request) {  
            
            $validator = Validator::make($request->all(), [
                'subject_name' => 'required|max:25',
            ]);
        
            if ($validator->fails()) {
                return redirect('/subjects')
                    ->withInput()
                    ->withErrors($validator);
            }
            
            $subject = new Subject;
            $subject->name = $request->subject_name;
            $subject->active = 1;
            $subject->questions_number = 0;
            $subject->save();

            return redirect('/subjects')->with('success', 'Subject added!');
        });
        Route::post('/question', function (Request $request) {  
            $validator = Validator::make($request->all(), [
                'question' => 'required|max:500',
                'answers' => 'array|min:2'
            ]);

            $subjectId = $request->subjectId;        
            if ($validator->fails()) {
                return redirect('/questions/'.$subjectId)
                    ->withInput()
                    ->withErrors($validator);
            }
            $answers = $request->answer;
            $correct = $request->correct;
            
            //regular validator fails with array validation :-(

            if(count(array_filter($answers)) < 2){//1st rule received at least 2 none-empty answers
                return redirect('/questions/'.$subjectId)
                ->withInput()
                ->with('error', 'Oops! Somethins wrong, please check your questions!');
            };
            
            if($answers[$correct[1]] == NULL ){//2nd rule radiobutton must correspond to none-empty answer field
                return redirect('/questions/'.$subjectId)
                ->withInput()
                ->with('error', 'Oops! Somethins wrong, please check your questions!');
            }
            // after validation actual adding new question
            $question = new Question;
            $question->subject_id = $subjectId;
            $question->user_id = Auth::id();
            $question->name = $request->question;
            $question->active = 1;
            $question->approved = 1;
            $question->save();
            $questionId = $question->id;
            foreach ($answers as $key => $value){
                $answer = new Answer;
                $answer->question_id = $questionId;
                $answer->name = $answers[$key];
                $answer->active = 1;
                $correctAnsw = $correct[1] == $key ? 1 : 0;
                $answer->correct = $correctAnsw;
                if($answer->name != NULL){
                    $answer->save();
                }
            }
            //////need to count answers and save it  to subjects table here
            $subjects = Subject::orderBy('created_at', 'asc')->get();
            foreach ($subjects as $subject) {
                $subjId = $subject->id;
                $count = Question::where('subject_id','=', $subjId)->count();

                $subject = Subject::find($subjId);
                $subject->questions_number = $count;
                $subject->save();
            }
            return redirect('/questions/'.$subjectId)->with('success', 'Question added!');
        });
        Route::post('/api/answerquestionedit', 'QuestionsController@editQuestion');
        Route::post('/api/saveanswer', 'QuestionsController@saveAnswer');
        Route::post('/api/savesubject', 'QuestionsController@saveSubject');
        Route::post('/api/savesubjectactive', 'QuestionsController@saveSubjectActive');
        Route::post('/api/activequestionedit', 'QuestionsController@editActiveQuestion');
        Route::post('api/activequestioneditbyid', 'QuestionsController@editActiveQuestionByid');
        Route::delete('/subject/{id}', function ($id) {  Subject::findOrFail($id)->delete(); return redirect('/subjects'); });
        Route::get('/questions/{id}', 'QuestionsController@showsubject');
        Route::get('/questions', 'QuestionsController@index')->name('questions');
        //Route::get('/questions_edit', 'QuestionsController@abc')->name('questions_edit');
        Route::get('/questions_edit', function(){ $subjects = Subject::orderBy('created_at', 'asc')->get(); return view('questions_edit', ['subjects' => $subjects]);})->name('questions_edit');
        //Route::get('/questions_edit/{id}/{act?}', 'QuestionsController@showsubjectedit');
        Route::get('/questions_edit/{id}/{act?}', function($id, $act=1){
            $subjects = Subject::orderBy('created_at', 'asc')->get();
            switch ($act) {
                case 1:
                    $givenSubjectAllQuestions = Question::where('subject_id', $id)->paginate(15);//////////////////////////////////////////////////pagination
                    break;
                case 2:
                    $givenSubjectAllQuestions = Question::where('subject_id', $id)->where('active', 1)->paginate(15);
                    break;
                case 3:
                    $givenSubjectAllQuestions = Question::where('subject_id', $id)->where('active', 0)->paginate(15);
                    break;
                default :
                    $givenSubjectAllQuestions = Question::where('subject_id', $id)->paginate(15);
            }
            
            return view('questions_edit', ['subjects' => $subjects, 'id' => $id, 'questions' => $givenSubjectAllQuestions, 'act' => $act]);
        });
        Route::get('/questions_upload', 'QuestionsController@questionsUpload')->name('questions_upload');///this is for transforming questions from older versions
        Route::get('/api/answer/{id?}', function ($id = 1) {//returns answer with sertain id
            return new AnswerResource(Answer::find($id));
        });
        Route::get('/api/answerstoquestion/{id?}', function ($id = 1) {//returns answers to sertain question id
            return new AnswerToQuestionResource(Answer::where('question_id', $id)->get());
        });
    });
    Route::get('/testresults/{id?}/{timingId?}/{itemsId?}/{daysId?}', 'ResultsController@index')->name('testresults');
});


