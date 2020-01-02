<?php
namespace App\Traits;
use \Crypt;
use Config;
use Carbon;
use Illuminate\Contracts\Encryption\DecryptException;

trait Encryptable
{
    public $randomkey = '';

    public function getAttribute($key)
    {
      try {
    		$value = parent::getAttribute($key);
    		if (in_array($key, $this->encryptable) && $this->randomkey != null ) {
                    $newEncrypter = new \Illuminate\Encryption\Encrypter($this->randomkey, Config::get( 'app.cipher' ) );
    				$value = $newEncrypter->decrypt($value);
    		 }else {
               $value=$value;
             }

             //check if it is phone column then change format to show
             if($key == 'phone'){
                $value = phone_number_format($value);
             }

             //check if it is date field then change its format
             if(isset($this->dates_need_to_be_changed) && in_array($key, $this->dates_need_to_be_changed) && !empty($value)){
                if(!isset($_COOKIE['client_timezone'])){
                    $timezone=Config::get('app.timezone');
                }
                else {
                    $timezone=$_COOKIE['client_timezone'];
                }

                // if($key != 'dob' && $key != 'expiration_date' && $key != 'effective_date')
                //     $value = \Carbon\Carbon::parse($value)->timezone($timezone)->format('M d, Y');
                // else
                $value = \Carbon\Carbon::parse($value)->timezone($timezone)->format('m-d-Y');
             }
            return $value;
        } catch (DecryptException $e) {
            if (isset($this->dontThrowDecryptException) && $this->dontThrowDecryptException === true) {
                return;
            }
            throw $e;
        }
    }

    public function setAttribute($key, $value)
    {  
		if (in_array($key, $this->encryptable)  && $this->randomkey != null) {
            $newEncrypter = new \Illuminate\Encryption\Encrypter($this->randomkey, Config::get( 'app.cipher' ) );
            if($value !=''){
                $value = $newEncrypter->encrypt($value);
            }
            else {
                $value = $value;
            }
        }
        return parent::setAttribute($key, $value);
    }

    public function calc(string $randomData) {
        $randomData=salt($randomData);
		$this->randomkey = $randomData;
    }
}



?>
