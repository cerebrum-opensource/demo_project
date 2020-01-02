<div class="fulldivlogin">
    <div class="logologin">
        <img src="../images/logo-login.png"  alt=""/> 
    </div>
    <div class="headingpage">Reset Password</div>
    <div class="innerfields">
        <form method="POST" id="reset-password" action="{{ route('reset-password-otp') }}" data-type="reset-password">
            @csrf   
            <div class="textfieldglobal">
                <input type="password" placeholder="Enter new password" class="form-control" name="password" autofocus>
                <span class="error" style="color:red;display:none;"></span>
            </div>
            <div class="clearfix"></div>
            <div class="textfieldglobal">
                <input type="password" placeholder="Confirm password" class="form-control" name="password_confirmation">
                <span class="error" style="color:red;display:none;"></span>
            </div>
                            
            <div class="buttonsbottom">
              <button class="next" type="submit">{{ __('Reset Password') }}</button>
              <a href="{{ route('login') }}" class="close">Cancel</a>
            </div>
        </form>
    </div>
</div>
