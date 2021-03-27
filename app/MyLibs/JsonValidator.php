<?php

namespace App\MyLibs;

use App\TemporaryTestingQuestions;
use App\TestingSession;
use App\TestingResults;
use Illuminate\Support\Facades\DB;

class JsonValidator {
   public function answersValidation($answers){
        ///////1 check how many items pushed from form, suppose to be 4
        $count = count($answers);
        if($count != 4){
            $result = "error";
            $description = "wrong number of answers;";
            return [ 'success' => $result , 'description' => $description ];
        }
        ////////2 check how many non-empty items pushed from form, suppose to be more or equal 2
        $nonEmptyCount = 0; 
        foreach ($answers as $answ) {
            if($answ["answer"]){
                $nonEmptyCount += 1;
            }
        }
        if($nonEmptyCount == 1){
            $result = "error";
            $description = "wrong number of none-empty answers";
            return [ 'success' => $result , 'description' => $description ];
        }
        ////////3 check if correct checked answer is non-empty
        foreach ($answers as $answ) {
            if($answ["answer"] == "" && $answ["correct"] == "true"){
                $result = "error";
                $description = "correct answer suppose to be non-empty";
                return [ 'success' => $result , 'description' => $description ];
            }
        }
        $result = "succcess";
        $description = "no errors"; 
        return [ 'success' => $result , 'description' => $description ];
   }
}