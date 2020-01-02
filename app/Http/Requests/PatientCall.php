<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PatientCall extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // echo date("m-d-Y",strtotime("-100 year"));exit;
        // $contact_date = explode('-', $this->contact_date);
        // $contact_time = explode(' ', $this->contact_time);
        // $contact_time = explode(':', $contact_time[0]);
        
        if(!$_COOKIE['client_timezone']){
            $timezone=Config::get('app.timezone');
        }
        else{
            $timezone=$_COOKIE['client_timezone'];
        }
        $nowDate = \Carbon\Carbon::now()->timezone($timezone)->format('m-d-Y');        
        $contactCheckDate = \Carbon\Carbon::now()->addDay()->timezone($timezone)->format('m-d-Y');
        $assessmentCheckDate = \Carbon\Carbon::now()->subDay()->timezone($timezone)->format('m-d-Y');
        $contactCheckTime = \Carbon\Carbon::now()->addMinute()->timezone($timezone)->format('g:i A');
        $assessmentCheckTime = \Carbon\Carbon::now()->subMinute()->timezone($timezone)->format('g:i A');

        if($this->get('contact_date') == $nowDate){
            $rules['contact_time'] = "required|date_format:g:i A|before:".$contactCheckTime;
        }else{
            $rules['contact_time'] = "required|date_format:g:i A";
        }

        if($this->get('assessment_date') == $nowDate && $this->get('agree') == 'yes'){
            $rules['assessment_time'] = "nullable|required_if:agree,yes|date_format:g:i A|after:".$assessmentCheckTime;
        }else{
           $rules['assessment_time'] = "nullable|required_if:agree,yes|date_format:g:i A";
        }
        

        $rules['comment'] = "required|max:10000";			
		$rules['agree'] = "required";					
        $rules['contact_date'] = "required|date_format:m-d-Y|before:".$contactCheckDate;		
		$rules['location'] = "nullable|required_if:agree,yes|max:1000";		    
		$rules['assessment_date'] = "nullable|required_if:agree,yes|date_format:m-d-Y|after:".$assessmentCheckDate;

        return $rules;	
        
    }
}
