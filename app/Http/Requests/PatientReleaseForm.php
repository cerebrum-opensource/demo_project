<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rule;

class PatientReleaseForm extends FormRequest
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

        $rules['form_type'] = 'required|'.Rule::in(['consent','hippa']);
        if ($this->has('form_type') && $this->get('form_type') == 'consent') {
            $rules['consent_form_signature_setup'] = "required";
            $rules['acknowledge_receive_services'] = 'required|in:1';
            $rules['acknowledge_emergency_medical_services'] = 'required|in:1';
            $rules['acknowledge_release_medical_records'] = 'required|in:1';
            $rules['acknowledge_release_vehicle'] = 'required|in:1';
            $rules['acknowledge_patient_bill_of_rights'] = 'required|in:1';
            $rules['acknowledge_signature'] = 'required|in:1';
            $rules['consent_form_documents_located_at_with'] = "required";
            $rules['consent_form_living_will_executed'] = "required";
            $rules['consent_form_dpoa_executed'] = "required";
            $rules['consent_form_signature_date'] = "required";
            $rules['consent_form_patient_initials'] = 'required|max:10';
            $rules['patient_id'] = "required";
            $rules['consent_form_living_will_executed'] = "required";
            $rules['consent_form_dpoa_name'] = 'required|max:50|regex:/^[\pL\s]+$/u';
            $rules['consent_form_dpoa_phone_number'] = 'required|phone';
        }
        else if($this->has('form_type') && $this->get('form_type') == 'hippa' ){
            $rules['hippa_form_signature_setup'] = "required";
            $rules['acknowledge_authorize_person'] = 'required|in:1';
            $rules['acknowledge_description_of_info'] = 'required|in:1';
            $rules['acknowledge_purpose_to_use'] = 'required|in:1';
            $rules['acknowledge_validity_of_form'] = 'required|in:1';
            $rules['acknowledge_signature'] = 'required|in:1';        
            $rules['patient_id'] = "required";

        }

        return $rules;
        
    }

    public function messages(){
        return [
            
        ];
    }
}
