<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Subject;

class QuestionsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
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
}