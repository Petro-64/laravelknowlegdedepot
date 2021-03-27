@extends('layouts.app')

@section('content')
    <!-- Begin page content -->
    <main role="main" class="container">
      <h1 class="mt-3">Edit Questions</h1>
      <div id="questionsEditWrapper">
      @if (count($subjects) > 0)
      <label for="subjectsSelect">Select subject you want to edit existing question:</label>
        @include('common.subjectsdropdown')
       
          @isset($id)
            <table id="questionsEditTable" class="table"><thead><tr><td>Question Id</td><td>Status(click to change)</td><td>Question</td><td>Edit</td></tr></thead>
              @if ($id != 0)
                @foreach ($questions as $question)
                  @php
                    if($question['active'] == 1){
                      $phraze = "active";
                    } else {
                      $phraze = "unactive";
                    }
                  @endphp
                  <tr><td class="questionId" data-id="{{ $question['id'] }}">{{ $question['id'] }}</td><td class="controlActivness" data-active="{{ $question['active'] }}">{{ $phraze }}</td><td class="questionText" data-id="{{ $question['id'] }}">{{ $question['name'] }}</td>
                      <td><button type="button" class="btn btn-primary btn-sm show-answers" data-id="{{ $question['id'] }}">Edit</button></td></tr>
                @endforeach
              @endif
            </table>
            {{ $questions->links() }}
          @endisset
      @endif
      </div>
      <div class="questionWrapper"><textarea class="form-control" rows="1"></textarea></div>
      <div id="editForm"><button type="button" class="btn btn-danger btn-sm">Cancel</button><button type="button" class="saveQuestion btn btn-primary btn-sm">Save</button>
      <div class="tabWrapper"></div>
    </div>
    <form class="hiddenForm">
        {{ csrf_field() }}
        <input type="hidden" id="questionId">
   </form>
    </main>
    <script>
      $( document ).ready(function() {
        $( "#subjectsSelect" ).change(function() {
          window.location.href = "/questions_edit/" + $( this ).val();
        });
        $(".btn-danger").on("click", function(){
          $("#questionsEditWrapper").css("display", "block");
          $("#editForm").css("display", "none");
        });
        $("td.controlActivness").on("click", function(){
          var active = $(this).attr("data-active");
          active == 1 ? activeValue = 0 : activeValue = 1;
          var questionIdValue = $(this).parent().find("td.questionId").text();
          var tokenValue = $(".hiddenForm input").val();
          var jqxhr = $.post( "/api/activequestionedit", {_token: tokenValue, active: activeValue, questionId: questionIdValue})
              .done(function() {
                location.reload();
              })
              .fail(function() {
                alert("Network error, please try again later");
              });
        })
        $(".show-answers").on("click", function(){
          var id = $(this).attr("data-id");
          $("#questionId").val(id);
          $(".questionWrapper textarea").val($(this).parent().parent().find(".questionText").text() );
          var requestUrl = "../api/answerstoquestion/" + id;
          var jqxhr = $.get( requestUrl )
          .done(function(data) {
            var row = '<table class="table-bordered"><tr>';
            var radioRow = '<tr>';
            var cellClass = '';
            $.each(data.data, function( index, value ) {
              if(value.correct == 1){
                cellClass = ' class="correct"';
                checked = ' checked ';
              } else {
                cellClass = ' class="uncorrect"';
                checked = '';
              }
              row = row + '<td' + cellClass + '><textarea class="form-control" rows="8" data-answerId="' + value.id + '">' + value.text + '</textarea></td>';
              radioRow = radioRow + '<td' + cellClass + ' data-order="' + index + '"><input type="button" value="Save" class="saveAnswer btn btn-primary"></td>'
            });
            radioRow = radioRow + '</tr>';
            row = row + '</tr>' + radioRow + '</table>';
            $(".tabWrapper").html(row);
            $("#questionsEditWrapper").css("display", "none");
            $("#editForm").css("display", "block");
          })
          .fail(function() {
            alert( "Please try again later" );
          })
        })
        function validate(){
          return true;
        }
        ////////////////////////////////////save particular answer start/////////////////////////
          $(document).on("click", ".saveAnswer" , function() {
            var $this = $(this);
            var tdNumber = parseInt($this.parent().attr("data-order")) + 1;
            var $this1 = $("table.table-bordered tr:nth-child(1) td:nth-child(" + tdNumber + ") textarea");
            var answer = $this1.val();
            var answId = $this1.attr("data-answerid");
            var tokenValue = $(".hiddenForm input").val();
            console.log("answId = ", answId);
            var jqxhr = $.post( "/api/saveanswer", {_token: tokenValue, answId: answId, answer: answer})
                .done(function() {
                  location.reload();
                })
                .fail(function() {
                  alert("Network error, please try again later");
                });
          })
          ////////////////////////////////////save particular answer end/////////////////////////
        $(".saveQuestion").on("click", function(){
          if(validate()){
            var tokenValue = $(".hiddenForm input").val();
            var questionValue = $(".questionWrapper textarea").val();
            var questionIdValue = $("#questionId").val();
            //////////////////////////here is to collect and combine data from 4 texareas start///////////////////
            jsonObj = [];
            var checked = $("table.table-bordered tr:nth-child(2) td input[name='correct']:checked").val();
            jsonObj["checkedId"] = checked; 
            var num = 4;
            for(i = 1; i<=4; i++){
              item = {};
              var currentAnswer = $("table.table-bordered tr:nth-child(1) td:nth-child(" + i + ") textarea").val();
              var currentId = $("table.table-bordered tr:nth-child(2) td:nth-child(" + i + ") input").val();
              var ifChecked = $("table.table-bordered tr:nth-child(2) td:nth-child(" + i + ") input[name='correct']").is(':checked');
              item["answer"] = currentAnswer;
              item["correct"] = ifChecked;
              item["id"] = currentId;
              jsonObj.push(item);
            }
            //////////////////////////here is to collect and combine data from 4 texareas end///////////////////
            var jqxhr = $.post( "/api/answerquestionedit", {_token: tokenValue, question: questionValue, questionId: questionIdValue, answers: jsonObj})
              .done(function() {
                location.reload();
              })
              .fail(function() {
                alert("Network error, please try again later");
              });
          }
        })
      });
    </script>
@endsection