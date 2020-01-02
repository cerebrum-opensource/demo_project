<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Patient;

class PatientChwAssessment extends FormRequest
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
    	if ($this->has('step_number') && $this->get('step_number') == '1') {
            
            if ($this->has('living_with_other') && $this->get('living_with_other') == 'on') {
                $rules['living_with_other_text'] = 'required|max:100';
            }else{
                $rules['lives_with'] = 'required|exists:manageable_fields,id,type,lives_with,deleted_at,NULL';
            }

            $rules['emergency_person1_name'] = "required|max:60|regex:/^[\pL\s\-\. ']+$/u";
            $rules['emergency_person1_relation'] = 'required|'.Rule::in(relationship_array_for_validation());
            $rules['emergency_person1_address'] = 'required|max:100';
            $rules['emergency_person1_address2'] = 'nullable|max:100';
            $rules['emergency_person1_city'] = 'required|max:50';
            $rules['emergency_person1_zip'] = 'bail|required|min:5|max:10|regex:/^[0-9]+$/u';
            $rules['emergency_person1_phone'] = 'required|phone';
            $rules['emergency_person1_state_id'] = 'required|exists:states,id';

            $rules['emergency_person2_name'] = "nullable|max:60|regex:/^[\pL\s\-\. ']+$/u";
            $rules['emergency_person2_address'] = 'nullable|max:100';
            $rules['emergency_person2_address2'] = 'nullable|max:100';
            $rules['emergency_person2_relation'] = Rule::in(relationship_array_for_validation());
            $rules['emergency_person2_city'] = 'nullable|max:50';
            $rules['emergency_person2_zip'] = 'bail|nullable|min:5|max:10|regex:/^[0-9]+$/u';
            $rules['emergency_person2_phone'] = 'nullable|phone';
            
            $rules['ed_visits_last_12_months'] = 'bail|required|min:0|integer|digits_between:1,3';
            $rules['ed_admissions_last_12_months'] = 'bail|required|min:0|integer|digits_between:1,3';


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
		return $rules;
		
        
    }

    // Here we can do more with the validation instance...
    public function withValidator($validator){

        $validator->after(function($validator)
        {
            try{           
                $patient_id = \Crypt::decrypt($this->get('patient_id'));
            } catch (DecryptException $e) {
                abort(404);
            exit;
            }

            if ($this->has('step_number') && $this->get('step_number') == '2') {
                if(!$this->has('pcp_not_required') && !$this->get('pcp_not_required') == 'on'){
                    $patient = Patient::find($patient_id);
                    $patient->calc($patient->random_key);
                    if(!$patient->pcp_id){
                        $validator->errors()->add('pcp_not_required', 'Add Primary Care Physician Information.');
                    }
                }
            } 
        });
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
