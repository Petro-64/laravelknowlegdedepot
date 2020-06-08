@extends('layouts.app')

@section('content')
    <main role="main" class="container">
    <h1 class="mt-3">Tests</h1>
    @isset($id) 
      <p>Maximum time you can spend on one question is 1 min. System informs you about time remaining. You can stop testing at any step. 
      System calculates amount of questions answered successfully and amount of failed questions. Please don't delete your browser's cookie during the testing. 
      We use cookie in order to collect information related to your testing. No personal information being collected. Press Start button to start testing or Cancel button to cancel. Happy testing! </p>
      <label for="subjectsSelect">Subject selected:</label>
    @else
      <p>To use a basic features of our system such as testing, no registration needed. </p>
      <p>If you woul'd like to have an advantage of keeping statistics of your test results or adding your own questions, we'll ask you to register here: <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a><p>
      <label for="subjectsSelect">First, please select subject you want to start testing:</label>
    @endisset

    @if (count($subjects) > 0)
      @include('common.subjectsdropdown')
    @endif

    @isset($id)
    <button type="button" class="btn btn-primary" id="startTestingButton">Start</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <button type="button" class="btn btn-danger" id="cancelTestingButton">Cancel</button>
    @endisset


    </main>
    <script>
      $( document ).ready(function() {
        $( "#subjectsSelect" ).change(function() {
          window.location.href = "/tests/" + $( this ).val();
        });

        $( "#cancelTestingButton" ).click(function() {
          window.location.href = "/tests";
        });

        $( "#startTestingButton" ).click(function() {
          window.location.href = "/testing";
        });
      });
    </script>
@endsection