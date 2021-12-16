@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header" style="color: green; font-weight: bold">Dashboard ffff More</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    @guest
                        You are guest
                        
                    @else
                        You are logged in!
                    @endguest
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
