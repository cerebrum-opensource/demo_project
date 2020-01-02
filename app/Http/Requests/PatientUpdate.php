<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PatientUpdate extends FormRequest
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
    	$rules = [];
    	if($this->has('email')){
            $this->merge([
                'email' => strtolower($this->get('email'))
            ]);
        }
        if ($this->has('is_care_team') && $this->get('is_care_team') == '1') {
        	$rules['assigned_chw'] = 'required|chw_user';
			$rules['assigned_cm'] = 'required|cm_user';
			$rules['assigned_md'] = 'required|md_user';

        }
        else {
            $patient_id = encrypt_decrypt('decrypt', $this->get("patient_id"));
        	$rules['first_name'] = "required|max:20|regex:/^[\pL\s\-\. ']+$/u";
			$rules['last_name'] = "required|max:40|regex:/^[\pL\s\-\. ']+$/u";
			$rules['patient_alias'] = 'nullable|max:50|regex:/^[\pL\s]+$/u';
			$rules['middle_initial'] = 'nullable|max:1|alpha';
			$rules['dob'] = 'required|date_format:m-d-Y|before:'.date("m-d-Y",strtotime("-10 year +1 days")).'|after:'.date("m-d-Y",strtotime("-100 year")).'';
			$rules['language'] = 'required';
			$rules['address_line1'] = 'required|max:100';
	    	$rules['address_line2'] = 'nullable|max:100';
	    	$rules['city'] = 'required|max:50';
	   		$rules['state_id'] = 'required|exists:states,id';
	    	$rules['zip_code'] = 'bail|required|min:5|max:5|regex:/^[0-9]+$/u';					
			$rules['image'] = 'image|mimes:jpeg,png,jpg|max:5120';
			$rules['phone'] = 'required|phone';
			$rules['gender'] ='nullable|'.Rule::in(gender_array());
			$rules['ssn'] = 'required|patient_unique_info:patients,ssn,'.$patient_id.'|regex:/^[1-9]\d{2}-\d{2}-\d{4}+$/u';
			$rules['email'] = 'nullable|email|unique:users,email|patient_unique_info:patients,email,'.$patient_id;	
			$rules['enrollment_status'] = 'required|'.Rule::in([ENROLLMENT_STATUS_INTENSE,ENROLLMENT_STATUS_MATURE,ENROLLMENT_STATUS_GRADUATE,ENROLLMENT_STATUS_DISCHARGE,ENROLLMENT_STATUS_ELOPED]);
        }
		return $rules; 
    }

    public function messages(){
        return [
            
        ];
    }
}
