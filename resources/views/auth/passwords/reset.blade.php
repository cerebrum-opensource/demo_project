@extends('layouts.login_layout')

@section('title')
    @if(app('request')->input('type') && app('request')->input('type') == 'set')
        Set Password
    @else
        Reset Password
    @endif
@endsection

@section('content')

<div class="wrapper">
  <div class="page-wrap">
    <div class="main">
      <div class="loginpage">
        <div class="container">
          <div class="subdivlogin">
            <div class="fulldivlogin">
                <div class="logologin">
                    <img src="{{ asset('images/logo-login.png') }}"  alt=""/> 
                </div>
                <div class="headingpage">
                    @if(app('request')->input('type') && app('request')->input('type') == 'set')
                        Set Password
                    @else
                        Reset Password
                    @endif
                </div>
                <div class="innerfields">
                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="form-group row">
                            <div class="col-md-12">
                                <input id="email"placeholder="{{ __('E-Mail Address') }}"  type="text" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ $email ?? old('email') }}" autofocus>

                                @if ($errors->has('email'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-12">
                                <input id="password" placeholder="{{ __('Password') }}" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" >

                                @if ($errors->has('password'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-12">
                                <input id="password-confirm" placeholder="{{ __('Confirm Password') }}" type="password" class="form-control {{ $errors->has('password_confirmation') ? ' is-invalid' : '' }}" name="password_confirmation" >
                                 @if ($errors->has('password_confirmation'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('password_confirmation') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Set Password') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
          </div>
        </div>      
       </div>
    </div>
  </div>
</div>
@endsection