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
                                    <!-- Task Name -->
                                    <td class="table-text">
                                        <div>{{ $subject->name }}</div>
                                    </td>
                                    <td class="table-text">
                                        <div>{{ $subject->active }}</div>
                                    </td>
                                    <td class="table-text">
                                        <div>{{ $subject->questions_number }}</div>
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

    </div>

    <!-- TODO: Current Tasks -->
    </main>
@endsection