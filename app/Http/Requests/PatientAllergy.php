<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

class PatientAllergy extends FormRequest
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

        if(!$_COOKIE['client_timezone']){
            $timezone=Config::get('app.timezone');
        }
        else{
            $timezone=$_COOKIE['client_timezone'];
        }

        $todayDate =  \Carbon\Carbon::now()->addDay()->timezone($timezone)->format('m-d-Y');
        if($this->has('allergy_id') && $this->get('allergy_id') != ''){

        }
        else{
           $rules['name'] = 'required|'.Rule::in(static_allergy_name());
           $rules['start_date'] = "required|date_format:m-d-Y|before:".$todayDate;  
        }
                 
        $rules['type'] = 'required|'.Rule::in(static_reaction_name());                   
        $rules['severity'] = 'required|'.Rule::in(static_severity_name());
        $rules['allergy_type'] = 'required|'.Rule::in([0,1]);                    
        $rules['comment'] = "required|max:1000";	
        

        if($this->get('start_date')) {
            $startDate =  \Carbon\Carbon::createFromFormat('m-d-Y',$this->get('start_date'))->subDay()->timezone($timezone)->format('m-d-Y');
            $rules['end_date'] = "nullable|date_format:m-d-Y|after:".$startDate;
        }
		
        return $rules;	
        
    }

    public function messages(){
        return [
            'name.required'=>'Enter allergy name.',
            'severity.required'=>'Select Severity.',
            'type.required'=>'Select type of reaction.',
            'type.in'=>'Reaction type is not valid',
            'allergy_type.in'=>'Allergy type is not valid',
            'start_date.required'=>'Select start date.',
        ];
    }
}
