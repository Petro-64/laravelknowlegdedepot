@extends('layouts.app')

@section('content')
    <!-- Begin page content -->
    <main role="main" class="container">
      <h1 class="mt-3">Test results</h1>
      <p class="lead">Welcome to Test results page</p>
      @guest
            To be able to collect your test results, you need to create account. <a class="nav-link" href="{{ route('register') }}">Register</a>
        @else
            Your test results here
        @endguest
    </main>
@endsection