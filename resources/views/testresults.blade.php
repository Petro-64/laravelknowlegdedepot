@extends('layouts.app')

@section('content')
    <!-- Begin page content -->
    <main role="main" class="container">
      <h1 class="mt-3">Test results</h1>
      <p class="lead">Welcome to Test results page11</p>
      @guest
            To be able to collect your test results, you need to create account. <a class="nav-link" href="{{ route('register') }}">Register</a>
        @else
            Your test results here
            <br/><br/>
        @endguest
        <div class="form-group resultsPage">
            <table>
                <tr>
                    <td>Subject to show</td>
                    <td>Sort by time</td>
                    <td>Items per page</td>
                    <td>Show tests for:</td>
                </tr>
                <tr>
                    <td>
                        <select class="form-control" id="subjectsSelect">
                            <option value="0">All subjects</option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}"
                                @isset($id)
                                    @if ($subject->id == $id ?? '')
                                    selected
                                    @endif
                                @endisset
                                >{{ $subject->name }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td>            
                        <select class="form-control" id="timingSelect">
                            <option value="0"
                                @isset($timingId)
                                    @if ($timingId == '0')
                                    selected
                                    @endif
                                @endisset>
                                Latest on top
                            </option>
                            <option value="1"
                                @isset($timingId)
                                    @if ($timingId == '1')
                                    selected
                                    @endif
                                @endisset>
                                Earliest on top
                            </option>
                        </select>
                    </td>
                    <td>            
                        <select class="form-control" id="itemsPerpage">
                            <option value="10"
                                @isset($itemsId)
                                    @if ($itemsId == '10')
                                    selected
                                    @endif
                                @endisset>
                                10
                            </option>
                            <option value="20"
                                @isset($itemsId)
                                    @if ($itemsId == '20')
                                    selected
                                    @endif
                                @endisset>
                                20
                            </option>
                            <option value="30"
                                @isset($itemsId)
                                    @if ($itemsId == '30')
                                    selected
                                    @endif
                                @endisset>
                                30
                            </option>
                        </select>
                    </td>
                    <td>            
                        <select class="form-control" id="daysId">
                            <!--<option value="7"
                                @isset($daysId)
                                    @if ($daysId == '7')
                                    selected
                                    @endif
                                @endisset>
                                Last week
                            </option>
                            <option value="30"
                                @isset($daysId)
                                    @if ($daysId == '30')
                                    selected
                                    @endif
                                @endisset>
                                Last month
                            </option>-->
                            <option value="180"
                                @isset($daysId)
                                    @if ($daysId == '180')
                                    selected
                                    @endif
                                @endisset>
                                Last 3 months
                            </option>
                        </select>
                    </td>
                </tr>
            </table>
        </div>
    @if (count($results) > 0)
            <div class="panel panel-default">
                <div class="panel-body">
                    <table class="table table-striped task-table">
                        <thead>
                            <th>Test date/time</th>
                            <th>Subject</th>                            
                            <th>Questions answered</th>
                            <th>Answers correct</th>
                            <th>Test score</th>
                        </thead>
                        <tbody>
                            @foreach ($results as $result)
                                <tr>
                                    @php
                                        $score = round( ($result->correct_questions_number / $result->answered_questions_number), 2);
                                    @endphp
                                    <td class="table-text">
                                        <div class="subj-name-unactive">{{ $result->created_at }}</div>
                                    </td>
                                    <td class="table-text">
                                        <div class="subj-name-unactive">{{ $result->name }}</div>
                                    </td>
                                    <td class="table-text">
                                        <div class="subj-name-unactive">{{ $result->answered_questions_number }}</div>
                                    </td>
                                    <td class="table-text">
                                        <div class="subj-name-unactive">{{ $result->correct_questions_number }}</div>
                                    </td>                                    
                                    <td class="table-text">
                                        <div class="subj-name-unactive">{{ $score }}</div>
                                    </td>
                                 </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            {{ $results->links() }}
        @endif


    </main>
    <script>
    $( document ).ready(function() {
        function createUrl(){
            var timingId = $( "#timingSelect" ).val();
            var itemsId = $( "#itemsPerpage" ).val();
            var subjectId = $( "#subjectsSelect" ).val();
            var daysId = $( "#daysId" ).val();
            window.location.href = "/testresults/" + subjectId + "/" + timingId + "/" + itemsId + "/" + daysId;
        }
        $( "#subjectsSelect" ).change(function() {
            createUrl();
        });

        $( "#timingSelect" ).change(function() {
            createUrl()
        });

        $( "#itemsPerpage" ).change(function() {
            createUrl()
        });

        $( "#daysId" ).change(function() {
            createUrl()
        });
    })
</script>
@endsection
