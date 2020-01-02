<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rule;

class PatientMedication extends FormRequest
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

        			
        $rules['frequency'] = "required|max:100";                   
        $rules['dosage'] = "bail|required|min:0|integer|digits_between:1,5";                    
		$rules['units'] = 'required|'.Rule::in(static_unit_name());
        $rules['comment'] = "required|max:1000";
        if($this->has('medication_id') && $this->get('medication_id') != 'null'){

        }
        else{
            $rules['name'] = 'required|'.Rule::in(static_medicine_name());
            $rules['start_date'] = "required|date_format:m-d-Y|before:".$todayDate;
        }
        

        if($this->get('start_date')) {
            $startDate =  \Carbon\Carbon::createFromFormat('m-d-Y',$this->get('start_date'))->subDay()->timezone($timezone)->format('m-d-Y');
            $rules['end_date'] = "nullable|date_format:m-d-Y|after:".$startDate;
        }

        return $rules;
        
    }

    public function messages(){
        return [
            'name.required'=>'Enter medication name.',
            'name.in'=>'Medication is not valid.',
            'start_date.required'=>'Select start date.',
           // 'start_date.before'=>'Select start date.',
        ];
    }
}
