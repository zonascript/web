@extends('layouts.landing', ['navBarOnly' => true, 'bodyClass' => 'degree-list', 'hideFooter' => true])

@section('content')

    <div class="container">
        <h1>Log In to BitDegree</h1>
        <div id="login-success" style="display: none">
            <div class="alert alert-success">Your instant log in link has been emailed to you.</div>
            <a href="{{ route_lang('home') }}" class="btn btn-default">Back to the Home Page</a>
        </div>
        <form action="{{ route_lang('login-post') }}" method="post" class="async-validated" data-show="#login-success">
            <div class="alert alert-danger other-error" style="display: none;">An unknown error occurred. Please make sure you entered correct data and try again.</div>
            @if(!empty($email))
                <div class="well-sm well">You already have an account at BitDegree! Click <strong>Log In</strong> below to authenticate.</div>
                <input type="hidden" name="email" value="{{ $email }}">
            @else
                <div class="form-group validation-email">
                    <label for="input-email">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="you@example.com" value="{{ $email }}">
                    <span class="validation-error"></span>
                </div>
            @endif

            @include('partials.recaptcha')

            <button class="btn btn-default">Log In</button> or <a href="{{ route_lang('signup') }}">Sign Up</a>
        </form>
    </div>

@endsection

@push('body-scripts')
    @include('partials.async-forms')
@endpush