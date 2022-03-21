<?php

namespace App\MyLibs;

use App\TemporaryTestingQuestions;
use App\TestingSession;
use App\TestingResults;
use Illuminate\Support\Facades\DB;

class TestResult {
   public function getSite() {
      return 'AmirHome1111.com';
   }
   public function createTestingSession($hash){
      $testingSession = new TestingSession;
      $testingSession->session_hash = $hash;
      $testingSession->save();
      return $testingSession->id;
   }
   public function createTemporaryQuestions($sessId, $question_id){
      $temporaryTestingQuestions = new TemporaryTestingQuestions;
      $temporaryTestingQuestions->session_id = $sessId;
      $temporaryTestingQuestions->question_id = $question_id;
      $temporaryTestingQuestions->answered = 0;
      $temporaryTestingQuestions->save();
   }
   public function createTestingResultItem($userId, $subjectId, $sessionId, $correct){
      $testingResults = new TestingResults;
      $testingResults->user_id = $userId;
      $testingResults->subject_id = $subjectId;
      $testingResults->testing_session_id = $sessionId;
      $testingResults->answered_questions_number = 1;
      $correct ? $testingResults->correct_questions_number = 1 : $testingResults->correct_questions_number = 0;
      $testingResults->save();
      return $testingResults->id;
   }

   public function createTestingResultItemEmpty($userId, $subjectId, $sessionId){ // this version is for react
      $testingResults = new TestingResults;
      $testingResults->user_id = $userId;
      $testingResults->subject_id = $subjectId;
      $testingResults->testing_session_id = $sessionId;
      $testingResults->answered_questions_number = 0;
      $testingResults->correct_questions_number = 0;
      $testingResults->save();
      return $testingResults->id;
   }

   public function removeEmptyResult($sessionId){// need to do this in order not to save empty result when answered quesions number = 0
      if(DB::table('testing_results')->where('testing_session_id', $sessionId)->first() !==null){
         $answered_questions_number = DB::table('testing_results')->where('testing_session_id', $sessionId)->first()->answered_questions_number;
         if($answered_questions_number == 0){
            DB::table('testing_results')->where('testing_session_id', $sessionId)->delete();
         }
      }
   }

   public function removeAllEmptyResults(){
      DB::table('testing_results')->where('answered_questions_number', 0)->delete();
   }

   public function updateTestingResultItem($testingSessionId, $correct){
      DB::table('testing_results')->where('testing_session_id', $testingSessionId)->increment('answered_questions_number');
      //dd("correct = ".$correct);
      if($correct == 1){
         DB::table('testing_results')->where('testing_session_id', $testingSessionId)->increment('correct_questions_number');
      }
   }

   public function getCurrentState($testingSessionId){
      $result = array();
      $ifRecordExists = TestingResults::where('testing_session_id', $testingSessionId)->first();
      if ($ifRecordExists == null){
         $correct = 0; $answered = 0;
      } else {
         $correct = TestingResults::where('testing_session_id', $testingSessionId)->first()->correct_questions_number;
         $answered = TestingResults::where('testing_session_id', $testingSessionId)->first()->answered_questions_number;
      }
      $result['correct'] = $correct; 
      $result['answered'] = $answered;
      return $result;
   }

   public function updateTime($testingSessionId){
      $tempTestQuestions = TestingSession::find($testingSessionId);
      $tempTestQuestions->touch();
   }
}