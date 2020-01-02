<?php

namespace App\Listeners;

use OwenIt\Auditing\Events\Auditing;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Config;

class AuditingListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Auditing  $event
     * @return void
     */
    public function handle(Auditing $event)
    {
        
        $baseClass = class_basename($event->model);
        $changeArray=[];
        $oldvalueArray=[];
        $changevalueArray=[];

        if($baseClass == 'Patient' && $event->model->getAuditEvent() == 'updated'){
            $Patient = new \App\Models\Patient;
            $newEncrypter = new \Illuminate\Encryption\Encrypter(salt($event->model->random_key), Config::get( 'app.cipher' ) );
            $oldvalue=$event->model->getOriginal();
            $changevalue=$event->model->getChanges();
            foreach ($oldvalue as $key => $value) {
                # code...
                 if(in_array($key,$Patient->getEncryptableValue())){
                    if($value !=''){
                        $oldvalueArray[$key]=$newEncrypter->decrypt($value); 
                    }
                    else {
                        $oldvalueArray[$key]=$value; 
                    }
                    
                  }
                  else {
                    $oldvalueArray[$key]=$value; 
                  }
            } 
            foreach ($changevalue as $key => $value) {
                # code...
                 if(in_array($key,$Patient->getEncryptableValue())){
                    if($value !=''){
                        $changevalueArray[$key]=$newEncrypter->decrypt($value); 
                    }
                    else {
                        $changevalueArray[$key]=$value; 
                    }
                    
                  }
                  else {
                    $changevalueArray[$key]=$value; 
                  }     
            }   
        }

        else if($baseClass == 'PatientInsurance' && $event->model->getAuditEvent() == 'updated'){

            $Patient = new \App\Models\PatientInsurance;
            $newEncrypter = new \Illuminate\Encryption\Encrypter($event->model->randomkey, Config::get( 'app.cipher' ) );


            $oldvalue=$event->model->getOriginal();
            $changevalue=$event->model->getChanges();
            foreach ($oldvalue as $key => $value) {
                # code...
                 if(in_array($key,$Patient->getEncryptableValue())){
                    if($value !=''){
                        $oldvalueArray[$key]=$newEncrypter->decrypt($value); 
                    }
                    else {
                        $oldvalueArray[$key]=$value; 
                    }
                    
                  }
                  else {
                    $oldvalueArray[$key]=$value; 
                  }
            } 
            foreach ($changevalue as $key => $value) {
                # code...
                 if(in_array($key,$Patient->getEncryptableValue())){
                    if($value !=''){
                        $changevalueArray[$key]=$newEncrypter->decrypt($value); 
                    }
                    else {
                        $changevalueArray[$key]=$value; 
                    }
                    
                  }
                  else {
                    $changevalueArray[$key]=$value; 
                  }     
            }   
        }
        else {
            return true;
        }
        foreach ($changevalueArray as $key => $value) {
        if($key!='case_number' && $key!='random_key' && $oldvalueArray[$key] != $value)
                if($key !='step_number'){
                    $changeArray[$key] = $value; 
                }
                
        }      
        if(count($changeArray) > 1){
            return true;
        }
        elseif (count($changeArray) == 1 && isset($changeArray['is_insured'])) {
            return true;
        }
        else {
            return false;
        }
  
    }
}
