<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Answer;
use App\TestingResults;
use App\MyLibs\JsonValidator;
use Illuminate\Support\Facades\DB;
use App\Subject;
use Auth;
use Carbon\Carbon;

class ResultsController extends Controller
{
    public function index($id = 0, $timingId = 0, $itemsId = 10, $daysId = 180)
    {
        
        $date = \Carbon\Carbon::today()->subDays($daysId);
        $userId = Auth::id();
        $subjects = Subject::orderBy('created_at', 'asc')
                            ->where('active', '=', '1')
                            ->get();
        if($id == 0){
            $operator = '<>';
            $value = "0";
        } else {
            $operator = '=';
            $value = $id;
        }

        if($timingId == 0){
            $timing = 'desc';
        } else {
            $timing = 'asc';
        }

        $testingResults = DB::table('testing_results')
                                ->join('subjects', 'testing_results.subject_id', '=', 'subjects.id')
                                ->select('testing_results.id', 'testing_results.answered_questions_number', 'testing_results.correct_questions_number','testing_results.created_at', 'subjects.name')
                                ->where('testing_results.user_id', '=', $userId)
                                ->where('testing_results.subject_id', $operator, $value)
                                //->where('testing_results.created_at', '>=', $date)
                                ->orderBy('testing_results.created_at', $timing)
                                ->paginate($itemsId);

        //dd($date);
        return view('testresults', ['results' => $testingResults, 'subjects'=> $subjects, 'id' => $id, 'timingId' => $timingId, 'itemsId' => $itemsId, 'daysId' => $daysId]);
    }
}
