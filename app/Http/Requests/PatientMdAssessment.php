<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\PatientMedication;
use App\Models\PatientAllergy;

class PatientMdAssessment extends FormRequest
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
            $rules['icd_code'] =  'required';
            $rules['icd_code.*'] =  'exists:icd_codes,id';
            if(!$this->has('substance_abuse_not_required') && !$this->get('substance_abuse_not_required') == 'on'){
                $rules['substance_abuse'] =   'required';
                $rules['substance_abuse.*'] =  Rule::in(static_substance_name());
            }	
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
            if(!$this->has('allergy_not_required') && !$this->get('allergy_not_required') == 'on'){
                $PatientAllergy = PatientAllergy::where('patient_id',$patient_id)->count();
                if($PatientAllergy == 0){
                    $validator->errors()->add('allergy_not_required', 'Add atleast one allergy.');
                }
                
            }
            if(!$this->has('medication_not_required') && !$this->get('medication_not_required') == 'on'){
                $patientMed = PatientMedication::where('patient_id',$patient_id)->count();
                if($patientMed == 0){
                    $validator->errors()->add('medication_not_required', 'Add atleast one medication.');
                }
                
            }
        });
    }
}
