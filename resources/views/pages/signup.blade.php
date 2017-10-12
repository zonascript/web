@extends('layouts.landing', ['navBarOnly' => true, 'bodyClass' => 'degree-list', 'hideFooter' => true])

@section('content')

    <div class="container">
        <h1>Sign Up at BitDegree</h1>
        <div id="signup-success" style="display: none">
            <div class="alert alert-success">Please check your email for verification link!</div>
            <a href="{{ route_lang('home') }}" class="btn btn-default">Back to the Home Page</a>
        </div>
        <form class="async-validated" action="{{ route_lang('signup-post') }}" method="post" data-show="#signup-success">
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

            <button class="btn btn-default" type="submit">Sign Up</button> or <a href="{{ route_lang('login') }}">Log In</a>
        </form>
    </div>

@endsection

@push('body-scripts')
    @include('partials.async-forms')
@endpush