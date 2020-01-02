<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Validator;
use App\Models\LastPassword;
use App\Models\User;
use App\Models\Patient;
use Auth;
use Hash;


class ValidatorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        // validation rule to check the password is not select from last 3 password
        $this->app['validator']->extend('old_password', function($attribute, $value, $parameters, $validator) {
            $valid=1;
            if(!empty($parameters)){
                $passwords = LastPassword::where('type_id','=',$parameters['1'])->where('type','=',$parameters['0'])->orderBy('id', 'desc')->take(3)->get();

                foreach ($passwords as $key => $password) {
                    if(Hash::check($value, $password->password)){
                        $valid=0;  
                    }
                }
                
            }
            else {
                $passwords = LastPassword::where('type_id','=',$parameters['1'])->where('type','=','user')->orderBy('id', 'desc')->take(3)->get();

                foreach ($passwords as $key => $password) {
                    if(Hash::check($value, $password->password)){
                        $valid=0;  
                    }
                }
            }
            if($valid){
                return true;
            }
            else {
                return false; 
            }

        });


        // validation rule to check the patient unique email or ssn after encrypted the data
        $this->app['validator']->extend('patient_unique_info', function($attribute, $value, $parameters, $validator) {
            $valid=1;
            if($parameters['1'] == 'email'){
                $value=strtolower($value);
            }
            if(!empty($parameters)){
                $patients = Patient::all();
                $patient_array=[];
                foreach ($patients as $patient) {
                    $patient->calc($patient->random_key);
                    $patient[$parameters['1']] ? $patient_array[$patient->id]=$patient[$parameters['1']] : '';
                    
                }
                if(in_array($value, $patient_array)){
                    if(isset($parameters['2'])){
                        $keyV = array_flip($patient_array);
                        $key = $keyV[$value];
                        if($key == $parameters['2']){
                            $valid=1;
                        }
                        else {
                            $valid=0;
                        }
                    }
                    else {
                       $valid=0;  
                    }
                   
                }
               
            }
            if($valid){
                return true;
            }
            else {
                return false; 
            }

        });
        
        //check phone format eg: (999) 999-9999        
        $this->app['validator']->extend('phone', function($attribute, $value, $parameters, $validator) {
            return preg_match('%^(?:(?:\(?(?:00|\+)([1-4]\d\d|[1-9]\d?)\)?)?[\-\.\ \\\/]?)?((?:\(?\d{1,}\)?[\-\.\ \\\/]?){0,})(?:[\-\.\ \\\/]?(?:#|ext\.?|extension|x)[\-\.\ \\\/]?(\d+))?$%i', $value) && strlen($value) >= 10;
         });        

         //check selected CHW available and valid and active
        $this->app['validator']->extend('chw_user', function($attribute, $value) {
            $chw_user = User::whereHas('roles', function($q){
                $q->where('name', 'CHW');
            })->where('id', $value)->first();
            if($chw_user)
                return true;
            else
                return false;
         });         

        //check selected CM available and valid and active
        $this->app['validator']->extend('cm_user', function($attribute, $value) {
            $cm_user = User::whereHas('roles', function($q){
                $q->where('name', 'CM');
            })->where('id', $value)->first();
            if($cm_user)
                return true;
            else
                return false;
         });         

        //check selected MD available and valid and active
        $this->app['validator']->extend('md_user', function($attribute, $value) {
            $md_user = User::whereHas('roles', function($q){
                $q->where('name', 'MD');
            })->where('id', $value)->first();
            if($md_user)
                return true;
            else
                return false;
         });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
