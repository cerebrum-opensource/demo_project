<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PatientCmAssessment extends FormRequest
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
			$rules['advance_healthcare_on_file'] =  'required|'.Rule::in(array("yes"=>"yes","no"=>"no"));
            $rules['polst_on_file'] = 'required|'.Rule::in(array("yes"=>"yes","no"=>"no"));
            if($this->has('advance_healthcare_on_file') && $this->get('advance_healthcare_on_file') == 'yes'){
                $rules['advance_healthcare_checkboxes'] = 'required';
                $rules['advance_healthcare_checkboxes.*'] = Rule::in(static_check_box_for_advanced_directive());
            }
            if($this->has('polst_on_file') && $this->get('polst_on_file') == 'yes'){
               $rules['polst_checkboxes'] = 'required';
               $rules['polst_checkboxes.*'] = Rule::in(static_check_box_for_advanced_directive());
            }
			$rules['advance_healthcare_attorney_name'] = 'required|max:50|regex:/^[\pL\s]+$/u';
			$rules['advance_healthcare_attorney_phone'] = 'required|phone';
			$rules['advance_healthcare_attorney_relation'] = 'required|'.Rule::in(relationship_array_for_validation());
			
			
        	$rules['hospice_provider_id'] = 'nullable|exists:registries,id,deleted_at,NULL,type,hospice_providers';
        	$rules['home_health_provider_id'] = 'nullable|exists:registries,id,deleted_at,NULL,type,home_health_providers';
            if ($this->has('pcp_not_required') && $this->get('pcp_not_required') == 'on') {
        	   
            }
            else {
                $rules['pcp_id'] = 'required|exists:registries,id,deleted_at,NULL,type,pcp_informations';  
            }

		}

		if ($this->has('step_number') && $this->get('step_number') == '2') {
			$rules['patient_functioning'] = 'required';
			$rules['patient_functioning.*'] = 'exists:manageable_fields,id,type,patient_functioning,deleted_at,NULL';
			
			
			$rules['durable_medical_equipment.*'] = 'exists:manageable_fields,id,type,durable_medical_equipment,deleted_at,NULL';
            if ($this->has('durable_medical_equipment_other')) {
                $rules['durable_medical_equipment_other_text'] = 'required|max:100';
            }
            if ($this->has('identifying_issues_other') && $this->get('identifying_issues_other') == 'on') {
                $rules['identifying_issues_other_text'] = 'required|max:100';
            }else{
                $rules['identifying_issues'] = 'required';
            }
            $rules['identifying_issues.*'] = 'exists:manageable_fields,id,type,identifying_issues,deleted_at,NULL';
            $rules['patient_functioning_text'] = 'nullable|max:1000';
            $rules['durable_medical_equipment_text'] = 'nullable|max:1000';

		}
		return $rules;
		
        
    }
}
