@extends('layouts.app_no_navigation')

@section('content')
    <main role="main" class="container">
    <h1 class="mt-3">Testing</h1>
    <h4>Subject: {{ $subjectName }}</h4>
    <p>Just click proper answer</a>
    <p><b>Question:</b> {{ $question }}</p>
    @foreach ($answers as $answer)
    <p class="answer" data-id="{{ $answer['id'] }}" style="cursor: pointer">{{ $answer['name'] }}</p>
    @endforeach

    <button type="button" class="btn btn-danger" id="stopTestingButton">I'd like to stop testing
    @guest
    *
    @endguest
    </button>
    @guest
        <p>*If you're guest, your testing result will be lost</p>
    @endguest
    <script>
        $( document ).ready(function() {
            $( ".answer" ).click(function() {
                window.location.href = "/testing/" + $(this).attr("data-id");
            });

            $( "#stopTestingButton" ).click(function() {
                window.location.href = "/tests";
            });            
        });
    </script>
@endsection

