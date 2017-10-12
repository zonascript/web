@extends('layouts.landing', ['navBarOnly' => true, 'bodyClass' => 'degree-list login-page', 'hideFooter' => true])

@section('content')
<div class="main">
    <div class="container">
        <div class="login-wrap">
            <a href="{{ route_lang('home') }}" class="login-logo">
                <img class="logo" src="{{ asset('bitdegree-logo.png') }}" alt="BitDegree">
            </a>
            <h1>Register</h1>
            <div id="signup-success" style="display: none">
                <div class="alert alert-success">You are almost set. Please check the e-mail you provided during registration and click confirmation link in it.</div>
                <a href="{{ route_lang('home') }}" class="back-to-homepage btn btn-default"><i class="fa fa-chevron-left" aria-hidden="true"></i> Back to the Home Page</a>
            </div>
            <form class="async-validated" action="{{ route_lang('signup-post') }}" method="post" data-show="#signup-success">
                <p class="subtitle">Please provide some info to get Registered to Bitdegree</p>
                <div class="alert alert-danger other-error" style="display: none;">An unknown error occurred. Please make sure you entered correct data and try again.</div>
                <div class="form-group validation-email">
                    <label for="input-email">Email Address</label>
                    <input type="email" id="input-email" name="email" class="form-control" placeholder="you@example.com" value="{{ $email }}">
                    <span class="validation-error"></span>
                </div>
                <div class="form-group validation-first-name">
                    <label for="input-name">Your Name <span class="text-muted">(optional)</span></label>
                    <input type="text" id="input-name" name="name" class="form-control" value="">
                    <span class="validation-error"></span>
                </div>

                @include('partials.recaptcha')

                <button class="btn btn-default" type="submit">Sign Up</button>
                <a class="link" href="{{ route_lang('login') }}">or Log In</a>
            </form>
        </div>
    </div>
</div>
@endsection

@push('body-scripts')
    @include('partials.async-forms')
@endpush