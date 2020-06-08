@extends('layouts.app')

@section('content')
    <!-- Begin page content -->
    <main role="main" class="container">
      <h1 class="mt-3">Questions</h1>
      @if (count($subjects) > 0)
      <label for="subjectsSelect">Select subject you want to add new question to:</label>
        @include('common.subjectsdropdown')
        @isset($id)
          @if ($id != 0)

          @if(session('success'))
            <div class="alert alert-success">
                {!! session('success') !!}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
            </div>
          @endif
          
          @if(session('error'))
            <div class="alert alert-danger">
                {!! session('error') !!}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
            </div>
          @endif

          @include('common.errors')
          <form action="/question" method="POST" class="form-horizontal">
            {{ csrf_field() }}
            <div class="form-group">
              <table style="width: 100%; text-align: center">
                <tr><td colspan="2"><label for="task" class="col-sm-3 control-label">New Question</label></td></tr>
                <tr><td colspan="2"><textarea name="question" id="question-name" rows="3" cols="" class="form-control">{{ old('question') }}</textarea></td></tr>
                <tr data-click="first"><td colspan="2"><label for="first-radio">Answer 1</label></td></tr>
                <tr data-click="first"><td><textarea name="answer[]" id="answer-name-1" rows="3" cols="" class="form-control"></textarea></td><td style="width: 20%"><input type="radio" id="first-radio" name="correct" value="a0"></td></tr>
                <tr data-click="second"><td colspan="2"><label for="second-radio">Answer 2</label></td></tr>
                <tr data-click="second"><td><textarea name="answer[]" id="answer-name-2" rows="3" cols="" class="form-control"></textarea></td><td style="width: 20%"><input type="radio" id="second-radio" name="correct" value="a1"></td></tr>
                <tr data-click="third"><td colspan="2"><label for="third-radio">Answer 3</label></td></tr>
                <tr data-click="third"><td><textarea name="answer[]" id="answer-name-3" rows="3" cols="" class="form-control"></textarea></td><td style="width: 20%"><input type="radio" id="third-radio" name="correct" value="a2"></td></tr>
                <tr data-click="fourth"><td colspan="2"><label for="fourth-radio">Answer 4</label></td></tr>
                <tr data-click="fourth"><td><textarea name="answer[]" id="answer-name-4" rows="3" cols="" class="form-control"></textarea></td><td style="width: 20%"><input type="radio" id="fourth-radio" name="correct" value="a3"></td></tr>
                <tr><td colspan="2"><input name="subjectId" type="hidden" value="{{ $id }}"></td></tr>
                <tr style="height: 140px">
                  <td colspan="2">
                    <button type="submit" id="questionSubmit" class="btn btn-primary" disabled="disabled">
                        <i class="fa fa-plus"></i> Add Question
                    </button>
                  </td>
                </tr>
              </table>
            </div>
          </form>
          @endif
        @endisset
      @endif
    </main>
    <script>
      $( document ).ready(function() {
        $( "#subjectsSelect" ).change(function() {
          window.location.href = "/questions/" + $( this ).val();
        });
        $("tr").click(function(){
          var num = $(this).attr("data-click");
          if(num !="undefined"){
              $("#questionSubmit").removeAttr("disabled");
              var name = num + "-radio";
              $('input:radio[id=' + name + ']')[0].checked = true;
          }
        })
      });
    </script>
@endsection