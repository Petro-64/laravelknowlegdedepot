@extends('layouts.app')

@section('content')
    <!-- Begin page content -->
    <main role="main" class="container">
      <h1 class="mt-3">Subjects</h1>
      <!-- Bootstrap Boilerplate... -->

    <div class="panel-body">
        <!-- Display Validation Errors -->
        @include('common.errors')

        @if(session('success'))
            <div class="alert alert-success">
                {!! session('success') !!}
            </div>Mine works, I'm lucky ;-)
        @endif
        <!-- New Task Form -->
        <form action="/subject" method="POST" class="form-horizontal">
            {{ csrf_field() }}

            <!-- Task Name -->
            <div class="form-group">
                <label for="task" class="col-sm-3 control-label">New subject</label>

                <div class="col-sm-6">
                    <input type="text" name="subject_name" id="subject_name" class="form-control" value="{{ old('subject_name') }}">
                </div>
            </div>

            <!-- Add Task Button -->
            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-6">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Add Subject
                    </button>
                </div>
            </div>
        </form>
        <!-- Current Tasks -->
        @if (count($subjects) > 0)
            <div class="panel panel-default">
                <div class="panel-heading">
                    Current subjects' list
                </div>

                <div class="panel-body">
                    <table class="table table-striped task-table">

                        <!-- Table Headings -->
                        <thead>
                            <th>Subject</th>
                            <th>Active?</th>
                            <th>Number of questions</th>
                            <th>Delete</th>
                        </thead>

                        <!-- Table Body -->
                        <tbody>
                            @foreach ($subjects as $subject)
                                <tr>
                                    @php
                                        if($subject->active == 1){
                                        $phraze = "active";
                                        } else {
                                        $phraze = "unactive";
                                        }
                                    @endphp
                                    <td class="table-text">
                                        <div class="subj-name-unactive">{{ $subject->name }}</div>
                                        <div class="subj-name-active"><input type="text" class="textfieldSubjectVal" value="{{ $subject->name }}"></div>
                                    </td>
                                    <td class="table-text">
                                        <div class="deactivateSubject" data-id="{{ $subject->id }}" data-active="{{ $subject->active }}">{{ $phraze }}</div>
                                    </td>
                                    <td class="table-text">
                                        <div>{{ $subject->questions_number }}</div>
                                    </td>
                                    <td class="table-text">
                                        <div class="button-name-unactive"><button type="button" class="btn btn-warning start-success-name" data-id="{{ $subject->id }}">Edit subj name</button></div>
                                        <div class="button-name-active"><button type="button" class="btn btn-success save-success-name" data-id="{{ $subject->id }}">Save/Cancel</button></div>
                                    </td>
                                    <td>
                                    @if($subject->questions_number == 0)
                                    <form action="/subject/{{ $subject->id }}" method="POST">
                                        {{ csrf_field() }}
                                        {{ method_field('DELETE') }}
                                        <button class="btn btn-danger">Delete</button>
                                    </form>
                                    @else
                                        <button class="btn btn-default" style="cursor: no-drop">Delete</button>
                                    @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
        <form class="hiddenForm">
        {{ csrf_field() }}
        
   </form>
    </div>
    <!-- TODO: Current Tasks -->
    </main>
    <script>
      $( document ).ready(function() {
        $(".start-success-name").click(function(){
            console.log("edit quest clicked");
            $(this).css("display", "none");
            $(this).parent().parent().parent().find(".subj-name-unactive").css("display", "none");
            $(this).parent().parent().parent().find(".subj-name-active").css("display", "block");
            $(this).parent().parent().find(".button-name-active").css("display", "block");
        })

        $(".save-success-name").on("click", function(){
            var subjectValue = $(this).parent().parent().parent().find(".textfieldSubjectVal").val();
            var subjectIdValue = $(this).attr("data-id");
            var tokenValue = $(".hiddenForm input").val();
            var jqxhr = $.post( "/api/savesubject", {_token: tokenValue, subjectIdValue: subjectIdValue, subjectValue: subjectValue})
                .done(function(data) {
                    location.reload();
                })
                .fail(function() {
                    alert("Network error, please try again later");
                });
        })

        $(".deactivateSubject").on("click", function(){
            var subjectIdValue = $(this).attr("data-id");
            var subjectActiveValue = $(this).attr("data-active");
            var tokenValue = $(".hiddenForm input").val();
            console.log("tokenValue = ", tokenValue);
            console.log("subjectIdValue = ", subjectIdValue);
            var jqxhr = $.post( "/api/savesubjectactive", {_token: tokenValue, subjectIdValue: subjectIdValue, subjectActiveValue: subjectActiveValue})
                .done(function(data) {
                    location.reload();
                })
                .fail(function() {
                    alert("Network error, please try again later");
                });
        })
      })
    </script>
@endsection