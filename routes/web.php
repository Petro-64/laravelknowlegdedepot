<?php

use Illuminate\Support\Facades\Route;
use App\Subject;
use App\Question;
use App\Answer;
use Illuminate\Http\Request;

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
Route::get('/', function () {  return view('home'); });
Route::get('/contribution', function () { return view('contribution'); })->name('contribution');
Route::get('/home', 'HomeController@index')->name('home');
Route::get('/tests', 'TestsController@index')->name('tests');
Route::get('/tests/{id}','TestsController@showsubject');
Route::get('/testing','TestsController@testing');
Route::get('/testing/{id?}','TestsController@testing');
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
                'question' => 'required|max:250',
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
            return redirect('/questions/'.$subjectId)->with('success', 'Question added!');
        });

        Route::delete('/subject/{id}', function ($id) {  Subject::findOrFail($id)->delete(); return redirect('/subjects'); });
        Route::get('/questions/{id}', 'QuestionsController@showsubject');
        Route::get('/questions', 'QuestionsController@index')->name('questions');
    });
    Route::get('/testresults', function () { return view('testresults'); })->name('testresults');
});

