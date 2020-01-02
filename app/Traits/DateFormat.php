<?php

namespace App\Traits;

use Carbon;

trait DateFormat
{
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);
    	//check if it is date field then change its format
         if(isset($this->dates_need_to_be_changed) && in_array($key, $this->dates_need_to_be_changed) && !empty($value)){
            if(!$_COOKIE['client_timezone']){
                    $timezone = Config::get('app.timezone');
                }
                else {
                    $timezone = $_COOKIE['client_timezone'];
                }
             if($key == 'assessment_date_time')
                 $value = \Carbon\Carbon::parse($value)->timezone($timezone)->format('m-d-Y (h:s:i)');
             else
            $value = \Carbon\Carbon::parse($value)->timezone($timezone)->format('m-d-Y');
         }else{
           $value=$value;
         }
        return $value;
    }
}



?>
