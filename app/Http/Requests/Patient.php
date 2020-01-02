<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class Patient extends FormRequest
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
		
    	if ($this->has('step_number') && $this->get('step_number') == '1') {
			
 			// regex:/^[\pL\s]+$/u

			$rules['first_name'] = "required|max:20|regex:/^[\pL\s\-\. ']+$/u";
			$rules['last_name'] = "required|max:40|regex:/^[\pL\s\-\. ']+$/u";
			$rules['patient_alias'] = 'nullable|max:50|regex:/^[\pL\s]+$/u';
			$rules['middle_initial'] = 'nullable|max:1|alpha';
			
		
			$rules['dob'] = 'required|date_format:m-d-Y|before:'.date("m-d-Y",strtotime("-10 year +1 days")).'|after:'.date("m-d-Y",strtotime("-100 year")).'';
			
			$rules['language'] = 'required';
			//$rules['county'] = 'required';
			$rules['address_line1'] = 'required|max:100';
        	$rules['address_line2'] = 'nullable|max:100';
        	$rules['city'] = 'required|max:50';
       		$rules['state_id'] = 'required|exists:states,id';
        	$rules['zip_code'] = 'bail|required|min:5|max:5|regex:/^[0-9]+$/u';			
        	//$rules['zip_code'] = 'required|min:5|max:5|regex:/^[a-zA-Z0-9-_]+$/u';			
			$rules['image'] = 'image|mimes:jpeg,png,jpg|max:5120';
			
			$rules['emergency_person1_name'] = "nullable|max:60|regex:/^[\pL\s\-\. ']+$/u";
			
			$rules['emergency_person1_relation'] = Rule::in(relationship_array_for_validation());
			$rules['emergency_person2_name'] = "nullable|max:60|regex:/^[\pL\s\-\. ']+$/u";
			$rules['emergency_person1_address'] = 'nullable|max:100';
			$rules['emergency_person1_address2'] = 'nullable|max:100';
			$rules['emergency_person2_address'] = 'nullable|max:100';
			$rules['emergency_person2_address2'] = 'nullable|max:100';
			$rules['emergency_person2_relation'] = Rule::in(relationship_array_for_validation());
			$rules['emergency_person1_city'] = 'nullable|max:50';
			$rules['emergency_person2_city'] = 'nullable|max:50';
			$rules['emergency_person1_zip'] = 'bail|nullable|min:5|max:5|regex:/^[0-9]+$/u';
			$rules['emergency_person2_zip'] = 'bail|nullable|min:5|max:5|regex:/^[0-9]+$/u';
			$rules['phone'] = 'required|phone';
			$rules['emergency_person1_phone'] = 'nullable|phone';
			$rules['emergency_person2_phone'] = 'nullable|phone';
			$rules['gender'] ='nullable|'.Rule::in(gender_array());

			$rules['lives_with'] = 'nullable|exists:manageable_fields,id,type,lives_with,deleted_at,NULL';

			//same request is used for referral and registration but on registration we do not have referral sources field
			if (!$this->has('assignment_modal')) {
				$rules['referral_source'] = 'required|exists:registries,id,deleted_at,NULL,type,referral_sources';
			}		

		}

		if ($this->has('action') && $this->get('action') == 'add') {
			$rules['ssn'] = 'required|patient_unique_info:patients,ssn|regex:/^[1-9]\d{2}-\d{2}-\d{4}+$/u';
			$rules['email'] = 'nullable|email|max:45|unique:users,email|patient_unique_info:patients,email';
		}else{
			if($this->get('step_number') == '1'){
				$rules['ssn'] = 'required|patient_unique_info:patients,ssn,'.$this->get("patient_id").'|regex:/^[1-9]\d{2}-\d{2}-\d{4}+$/u';
				$rules['email'] = 'nullable|email|unique:users,email|patient_unique_info:patients,email,'.$this->get('patient_id');
			}
		}

		if ($this->has('living_with_other')) {
			$rules['living_with_other_text'] = 'required|max:100';
		}

		if ($this->has('step_number') && $this->get('step_number') == '2') {
			if ($this->has('patient_concern_other')) {
				$rules['patient_concern_other_text'] = 'required|max:100';
			}
			else
			{
				$rules['patient_concern'] = 'required';
				// need to add this if manageable field is need to be deleted from admin
				//$rules['patient_concern.*'] = 'exists:manageable_fields,id,type,patient_concern,deleted_at,NULL';
			}
			if ($this->has('icd_code')) {
				$rules['icd_code.*'] =  'exists:icd_codes,id';
			}
			
		}

		if ($this->has('step_number') && $this->get('step_number') == '3') {
		    $rules['insurances.0.insurance_id'] = 'nullable|required_if:is_insured,""|exists:registries,id,deleted_at,NULL,type,insurances';
		    $rules['insurances.0.policy'] = 'required_if:is_insured,""|max:100';
		    $rules['insurances.0.group'] = 'required_if:is_insured,""|max:100';
		    $rules['insurances.0.authorized_by'] = 'required_if:is_insured,""|max:60';
		    $rules['insurances.0.authorization'] = 'required_if:is_insured,""|max:100';
		    $rules['insurances.0.effective_date'] = 'required_if:is_insured,""';
		    $rules['insurances.0.expiration_date'] = 'required_if:is_insured,""';		    

			//secondary insurance
			if($this->get('is_insured') != 'on')
			{
				$rules['insurances.1.insurance_id'] = 'nullable|exists:registries,id,deleted_at,NULL,type,insurances';
				$rules['insurances.1.policy'] = 'required_with:insurances.1.insurance_id|max:100';
			    $rules['insurances.1.group'] = 'required_with:insurances.1.insurance_id|max:100';
			    $rules['insurances.1.authorized_by'] = 'required_with:insurances.1.insurance_id|max:60';
			    $rules['insurances.1.authorization'] = 'required_with:insurances.1.insurance_id|max:100';
			    $rules['insurances.1.effective_date'] = 'required_with:insurances.1.insurance_id';
			    $rules['insurances.1.expiration_date'] = 'required_with:insurances.1.insurance_id';
			}
			
			$rules['contract_payer'] = 'nullable|required_if:case_status,""|exists:registries,id,deleted_at,NULL,type,contract_payers';
			$rules['authorization_code'] = 'nullable|required_if:case_status,""|max:100';
		}
		
		if ($this->has('step_number') && $this->get('step_number') == '4') {
			$rules['category_id'] = 'required|exists:manageable_fields,id,type,document_category,deleted_at,NULL';
			$rules['document_name'] = 'required|max:60';
			$rules['uploaded_document'] = 'required|mimes:pdf|max:10240';
		}
		
		if ($this->has('step_number') && $this->get('step_number') == '5') {
				$rules['notes_area'] = 'required|max:100';
				$rules['notes_subject'] = 'required|max:1000';
		}

		return $rules;
		
        
    }

    public function messages(){
    	return [
    		'insurances.0.insurance_id.required_if'=>'Choose organization name.',
    		'insurances.0.policy.required_if' => 'Enter policy number.',
		    'insurances.0.group.required_if' => 'Enter group number.',
		    'insurances.0.authorized_by.required_if' => 'Enter name.',
		    'insurances.0.authorization.required_if' => 'Enter authorization number.',
		    'insurances.0.effective_date.required_if' => 'Choose effective date for primary insurance.',
		    'insurances.0.expiration_date.required_if' => 'Choose expiration date for primary insurance.',    		
		    
    		'insurances.1.policy.required_with' => 'Enter policy number.',
		    'insurances.1.group.required_with' => 'Enter group number.',
		    'insurances.1.authorized_by.required_with' => 'Enter name.',
		    'insurances.1.authorization.required_with' => 'Enter authorization number.',
		    'insurances.1.effective_date.required_with' => 'Choose effective date for secondary insurance.',
		    'insurances.1.expiration_date.required_with' => 'Choose expiration date for secondary insurance.',
		    'contract_payer.required_if' => 'Choose contracted payer',
		    'icdCode.*' => 'Please select this code also else remove it',
		    'insurances.1.insurance_id.exists'=>'The selected insurance is invalid.',
		    'insurances.0.insurance_id.exists'=>'The selected insurance is invalid.',
		];
    }
}
