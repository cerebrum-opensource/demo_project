@extends('layouts.login_layout')

@section('title')
  Login
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
				<img src="{{ asset('images/logo-login.png') }}"  alt=""/> </div>
				<div class="headingpage">{{ __('Login') }}</div>
				@if ($errors->has('msg'))
                        <span class="error" style="color:red">{{ $errors->first('msg') }}</span>
                 @endif
					<div class="innerfields">
						 @if (session('status'))
		                  <div class="alert alert-success alert-dismissible"> 
				                 <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
				  				 {{ session('status') }}
				  		  </div>
				  		@endif
						 <form method="POST" action="{{ route('login') }}" autocomplete="off" id="login_form">
                        @csrf
						<div class="textfieldglobal">
						   <input id="email" type="email" placeholder="Email Address" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}">
						    @if ($errors->has('email'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
						</div>
						<div class="textfieldglobal">
						  
						  <input id="password" type="password" placeholder="Password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" onfocus="this.removeAttribute('readonly');">

                                @if ($errors->has('password'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
						  
						<a class="forgotlink" href="{{ route('password.request') }}">
                                    {{ __('Forgot Your Password?') }}
                        </a>
						</div><div class="buttonsbottom">
						 <button type="submit" class="btn btn-primary login_form_button" onclick="this.disabled=true; this.form.submit();">
                                    {{ __('Login') }}
                          </button>
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

@section('script')
<script type="text/javascript">
	$(document).ready(function(){
		fadeOutAlertMessages();	
		$('body').on('click', '.login_form_button', function(e) {
        	document.getElementById('login_form').submit(); 
		}); 	
	}); 	
</script> 
@endsection