<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use App\Subject;
use App\Question;
use App\Answer;
use App\TestingSession;
use App\TemporaryTestingQuestions;
use App\TestingResults;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use App\MyLibs\TestResult;
use Auth;

class TestsOneController extends Controller
{

    private $testingSessionCookieName;
    private $subjectIdCookieName;
    protected $testResult;

    public function __construct(TestResult $testResult) {
        $this->testingSessionCookieName = Config::get('hashedcookiesnames.testingSessionCookieName');//need to hash cookie name to prevent cheating
        $this->subjectIdCookieName = Config::get('hashedcookiesnames.subjectId');
        $this->testResult = $testResult;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request) {
        $subjects = Subject::where('active', "1")->orderBy('created_at', 'asc')->get();
        Cookie::queue(Cookie::forget($this->testingSessionCookieName));
        Cookie::queue(Cookie::forget($this->subjectIdCookieName));
        return view('tests', ['subjects' => $subjects]);//, 'id' => $id
    }

    public function showsubject($id){
        $subjects = Subject::where('active', "1")->orderBy('created_at', 'asc')->get();
        Cookie::queue(cookie($this->subjectIdCookieName, $id));
        return view('tests', ['subjects' => $subjects, 'id' => $id]);//
    }

    public function testing($id = null){
        $subjectId = Cookie::get($this->subjectIdCookieName);
        if($subjectId == null){
            return redirect('/tests');
        };
        if($id != null){//it means that we came here with answer
            $correct = Answer::where('id', $id)->first()->correct;
            $hashedTestingSession = Cookie::get($this->testingSessionCookieName);
            if($hashedTestingSession == null){
                return redirect('/tests');
            };
            $testingSessionId = TestingSession::where('session_hash', $hashedTestingSession)->pluck('id')->first();
            $resultItem = TestingResults::where('testing_session_id', '=', $testingSessionId)->first();
            if ($resultItem === null) {//it means that this is the first question 
                $userId = Auth::id();
                is_null($userId) ? $userId = 4 : $userId = $userId; 
                $this->testResult->createTestingResultItem($userId, $subjectId, $testingSessionId, $correct);
                //dd("doesnt exists and subjectId = ".$subjectId);
             } else {
                $this->testResult->updateTestingResultItem($testingSessionId, $correct);
                //dd("exists");
             }
             $this->testResult->updateTime($testingSessionId);//to be able to remove temporary testing questions and testing sessions, we need to update "updated at" field
        } else {
            $hashedTestingSession = Str::random();
            Cookie::queue(cookie($this->testingSessionCookieName, $hashedTestingSession));
            $sessId = $this->testResult->createTestingSession($hashedTestingSession);
            $givenSubjectAllQuestions = Question::where('subject_id', '=', $subjectId)->where('active', '=', 1)->where('approved', '=', 1)->get();
            foreach ($givenSubjectAllQuestions as $value) {
                $this->testResult->createTemporaryQuestions($sessId, $value->id);
            }
        }

        $subjectName = Subject::where('id', $subjectId)->first();
        $testingSessionId = TestingSession::where('session_hash', $hashedTestingSession)->pluck('id')->first();
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
            shuffle($answersToShow);
            $quest->answered = 1;
            $quest->save();
        } else {
            //to clean up session and temporary questions
            //show proper message
            $answersToShow = [];
            $ifRemainQuestions = 0; 
            $currentQuestionId = null;
        }
        return view('testing', ['subjectName' => $subjectName['name'], 
                                'question' => htmlspecialchars_decode($questionToShow), 
                                'answers' => $answersToShow, 
                                'ifRemainQuestions' => $ifRemainQuestions,
                                'answered' => $currentState['answered'],
                                'correct' => $currentState['correct'],
                                'currentQuestionId' => $currentQuestionId
                                ]);
    }

    public function getCorrectAnswer($id){
        $answersToShow = Answer::select('correct','id')->where('question_id', $id)->get()->toArray();
        return response()->json(['data'=>$answersToShow]);
    }
}