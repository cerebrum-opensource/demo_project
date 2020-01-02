@extends('layouts.login_layout')


@section('title')
   Forgot Password
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
				<div class="headingpage">{{ __('Forgot Password') }}</div>
				<div class="innerfields">
				 <form method="POST" id="send-otp" action="{{ route('send-reset-otp') }}" data-type="send-otp">
                        @csrf	
				<div class="textfieldglobal">
					<input id="email" type="text" placeholder="Email Address" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" autofocus>
					<span class="error" style="color:red;display:none;"></span>
				</div>
				<div class="clearfix"></div>
				<span class="smalltextunderheading otp-enter-area" style="display:none;">Please enter verification code that has been sent to your mail</span>
				<div class="clearfix"></div>
				<div class="row otp-enter-area" style="display:none;">
					<div class="otpfields">
						<div class="col-12">
							<div class="textfieldglobal">
              					<input  type="text" placeholder="Enter 6-digits OTP here" maxlength="6" name="otp">
              					<span class="error" style="color:red;display:none;"></span>
								<a href="javascript:resend()" class="forgotlink ">Resend code</a>
            				</div>
            			</div>
					</div>
				</div>
					
				<div class="buttonsbottom">
				    <button class="next" type="submit">Submit</button>
					<a href="{{ route('login') }}" class="close">Cancel</a>
				</div>
			</div>
	    </div>
     </form>
</div>
</div>		</div>
      
    </div>
  </div>
</div>


@endsection


@section('script')
<script type="text/javascript">

	$(document).on('submit', '#send-otp, #reset-password',function(e){
		$(this).find('input[name=email]').attr('disabled',false);
		e.preventDefault();
	//	console.log(this);
		sendajax(new FormData($(this)[0]),$(this).attr('action'),$(this).attr('data-type'));
		$(this).find('input[name=email]').attr('disabled',"disabled");
	});

	function resend() {
		$('#send-otp').find('input[name=email]').attr('disabled',false);
		var formData = new FormData($('#send-otp')[0]);
		$('#send-otp').find('input[name=email]').attr('disabled',"disabled");
		$.ajax({
			url:"{{ route('resend-reset-otp') }}",
			data:formData,
		    processData: false,
		    contentType: false,
		    dataType: "json",
			success:function(data){	
				$('.innerfields').prepend('\
	              <div class="alert alert-success alert-dismissible"> \
	                  <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>\
	                  OTP sent successfully.\
	              </div>\
            	');
            	fadeOutAlertMessages();
			    //alertify.set('notifier','position', 'top-right');	
				//alertify.success('OTP sent successfully.');			
			},
			error:function(error){
				$('.innerfields').prepend('\
	              <div class="alert alert-danger alert-dismissible"> \
	                  <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>\
	                  Some problem while sending OTP, please try again.\
	              </div>\
            	');
            	fadeOutAlertMessages();			

			}
			
		});
	}	

	function sendajax(formData, url, type) {
		$.ajax({
			url:url,
			data:formData,
		    processData: false,
		    contentType: false,
		    dataType: "json",
			success:function(data){		
				if(type == 'send-otp'){
					$('form#send-otp').attr('data-type','verify-otp');
					$(".otp-enter-area").show();
			    	$("input[name=email]").attr("disabled","disabled");
			    	$('#send-otp').attr('action',"{{ route('verify-reset-otp') }}");
				}else if(type == 'verify-otp'){
					$('form#send-otp').attr('data-type','reset-password');
					$('form#send-otp').attr('action',"{{ route('reset-password-otp') }}");
					$('.subdivlogin').html("");
					$('.subdivlogin').html(data.html);
				}else{
					window.location.href = "{{ route('login') }}";
				}			
			},
			error:function(error){
				if(type == 'send-otp'){
					$("input[name=email]").attr("disabled",false);
			    }
			    $.each(error.responseJSON.errors,function(key,value){
	                $('input[name="'+key+'"]').parent().find('span.error').html(value).addClass('active').show();
	                $('select[name="'+key+'"]').parent().find('span.error').html(value).addClass('active').show();
	            });

	            jQuery('html, body').animate({
	                scrollTop: jQuery(document).find('.error.active:first').parent().offset().top
	            }, 500);

	        }
		});
	}

</script> 
@endsection
