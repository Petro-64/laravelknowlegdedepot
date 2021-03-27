<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Subject;
use App\Question;
use App\Answer;
use App\MyLibs\JsonValidator;
use Illuminate\Support\Facades\DB;


class QuestionsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $jsonvalidator;


    public function __construct(JsonValidator $jsonVal)
    {
        $this->middleware('auth');
        $this->jsonvalidator = $jsonVal;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $subjects = Subject::orderBy('created_at', 'asc')->get();
        return view('questions', ['subjects' => $subjects]);//, 'id' => $id
    }

    public function showsubject($id){
        $subjects = Subject::orderBy('created_at', 'asc')->get();
        return view('questions', ['subjects' => $subjects, 'id' => $id]);//
    }

    public function edit()
    {
        $subjects = Subject::orderBy('created_at', 'asc')->get();
        return view('questions_edit', ['subjects' => $subjects]);
    }

    public function showsubjectedit($id, $act=1){
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
    }

    public function editQuestion(Request $request){
        $bbb = $request->answers;
        $validationrespond = $this->jsonvalidator->answersValidation($bbb);
        $id = $request->questionId;
        $question = Question::find($id);
        $question->name = $request->question;
        $question->save();
        if($validationrespond['success'] == "succcess"){
            foreach ($bbb as $answ) {
                if($answ["id"]){
                    $curr = Answer::find($answ["id"]);
                    if($answ["answer"] != ""){
                        $curr->name = $answ["answer"]; 
                        $corString = $answ["correct"] == "true" ? "1" : "0";
                        $curr->correct = $corString;
                        $curr->save();
                    } else {
                        $res=$curr->delete();
                    }
                } else {
                    if($answ["answer"] != "" && $answ["correct"] == "true"){//case when addeed new question to epty field and checked it
                        $answer = new Answer;
                        $answer->question_id = $id;
                        $answer->name = $answ["answer"];
                        $answer->active = 1;
                        $answer->correct = 1;
                        $answer->save();
                    }
                }
            }
        }
        $data = $validationrespond;
        return response()->json($data);
    }

    public function saveAnswer(Request $request){// to be able to save particular answer
        $id = $request->answId;
        $answer = Answer::find($id);
        $answer->name = $request->answer;
        $answer->save();
        return response()->json(['success'=>$request->questionId]);
    }

    public function editActiveQuestion(Request $request){
        $id = $request->questionId;
        $question = Question::find($id);
        $question->active = $request->active;
        $question->save();
        return response()->json(['success'=>'Ajax request submitted successfully111']);
    }

    public function editActiveQuestionByid(Request $request){
        $id = $request->questionId;
        $question = Question::find($id);
        $active = $question->active; 
        $activeNew = $question->active == 1 ? $activeNew = 0 : $activeNew = 1;  
        $question->active = $activeNew;
        $question->save();
        return response()->json(['success'=>'Ajax request submitted successfully']);
    }

    public function questionsUpload(){//this is temporary controller to perform data import from older versions of testing system
        //$answers = DB::table('questionsMy')->orderBy('questID');
        /*$answers = DB::table('questionsMy')->orderBy('questID')->limit(1500)->get();
        foreach ($answers as $answer) {
            $file = '/var/www/travel_list/storage/logs/my.txt';
            $current = file_get_contents($file);
            $current .= $answer->questID."\n";///$bbb[0]["answer"].
            if($answer->subjId == 4){//HTML and CSS
                $question = new Question;
                $question->id = $answer->questID;
                $question->subject_id = 2;  ///javascript
                $question->user_id = 1;
                $question->name = $answer->questionText;
                $question->active = 1;
                $question->approved = 1;
                $question->save();
            }
        };
        */
        $bbb = DB::table('answersMy')->orderBy('answerID')->limit(5000)->get();
        foreach ($bbb as $ccc) {
            //$file = '/var/www/travel_list/storage/logs/my.txt';
            //$current = file_get_contents($file);
            //$current .= $answer->questID."\n";///$bbb[0]["answer"].
            //file_put_contents($file, $current);
            $answer = new Answer;
            $answer->id = $ccc->answerID;
            $answer->question_id = $ccc->questID;
            $answer->name = $ccc->answerText;
            $answer->active = 1;
            $answer->correct = $ccc->ifCorrect;
            $answer->save();
        };
    }

    public function saveSubject(Request $request){
        $subject = Subject::find($request->subjectIdValue);
        $subject->name = $request->subjectValue;
        $subject->save();
        return response()->json(['success'=>'Ajax request submitted successfully']);
    }

    public function saveSubjectActive(Request $request){
        $subject = Subject::find($request->subjectIdValue);
        $activeToChange = $request->subjectActiveValue == 1 ? 0 : 1;
        $subject->active = $activeToChange;
        $subject->save();
        return response()->json(['success'=>'Ajax request submitted successfully']);
    }
}