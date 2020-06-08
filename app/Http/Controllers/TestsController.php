<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use App\Subject;
use App\Question;
use App\Answer;
use App\TestingSession;
use App\TemporaryTestingQuestions;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use App\MyLibs\TestResult;

class TestsController extends Controller
{

    private $testingSessionCookieName;
    private $subjectIdCookieName;
    protected $testResult;

    public function __construct(TestResult $testResult)
    {
        $this->testingSessionCookieName = Config::get('hashedcookiesnames.testingSessionCookieName');//need to hash cookie name to prevent cheating
        $this->subjectIdCookieName = Config::get('hashedcookiesnames.subjectId');
        $this->questionIdCookieName = Config::get('hashedcookiesnames.questionId');
        $this->testResult = $testResult;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $subjects = Subject::orderBy('created_at', 'asc')->get();
        Cookie::queue(Cookie::forget($this->testingSessionCookieName));
        Cookie::queue(Cookie::forget($this->subjectIdCookieName));
        //dd($this->testResult->getSite());
        return view('tests', ['subjects' => $subjects]);//, 'id' => $id
    }

    public function showsubject($id){
        $subjects = Subject::orderBy('created_at', 'asc')->get();
        $hashedTestingSession = Str::random();
        Cookie::queue(cookie($this->testingSessionCookieName, $hashedTestingSession));
        Cookie::queue(cookie($this->subjectIdCookieName, $id));
        $testingSession = new TestingSession;
        $testingSession->session_hash = $hashedTestingSession;
        $testingSession->save();
        $sessId = $testingSession->id;
        $givenSubjectAllQuestions = Question::where('subject_id', $id)->get();
        foreach ($givenSubjectAllQuestions as $value) {
            $temporaryTestingQuestions = new TemporaryTestingQuestions;
            $temporaryTestingQuestions->session_id = $sessId;
            $temporaryTestingQuestions->question_id = $value->id;
            $temporaryTestingQuestions->answered = 0;
            $temporaryTestingQuestions->save();
        }
        return view('tests', ['subjects' => $subjects, 'id' => $id]);//
    }

    public function testing($id = null){
        $subjectId = Cookie::get($this->subjectIdCookieName);
        $testingSessionHash = Cookie::get($this->testingSessionCookieName);
        if($subjectId == null || $testingSessionHash == null){
            return redirect('/tests');
        };
        if($id != null){
            $correct = Answer::select('correct')->where('id', $id)->get(1)->toArray();
            //dd($correct[0]['correct']);
            //here to add this answer to results table to increment
        }
        $subjectName = Subject::where('id', $subjectId)->first();
        $testingSessionId = TestingSession::where('session_hash', $testingSessionHash)->pluck('id')->first();
        $questionToShow = '';
        $collection = TemporaryTestingQuestions::where('answered', 0)->where('session_id', $testingSessionId)->pluck('id');
        if (!$collection->isEmpty()) {
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
        }

        return view('testing', ['subjectName' => $subjectName['name'], 'question' => $questionToShow, 'answers' => $answersToShow]);
    }
}