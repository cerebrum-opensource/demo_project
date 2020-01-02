<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\PatientCareplan;
use Auth;

class Careplan extends FormRequest
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
        $rules['notes'] = "required|max:10000";
		return $rules;
    }



     // Here we can do more with the validation instance...
    public function withValidator($validator)
    {
        $role = Auth::user()->getRoleNames()[0];
        $user_id = Auth::user()->id;
        $validator->after(function($validator) use ($role,$user_id)
        {
            if($this->get('patient_id')){
               // $validator->errors()->add('notes_error', trans('message.not_compelte_assesment'));  
            }
            if($id = $this->get('careplan_id')){
                $patientCareplan = PatientCareplan::find(encrypt_decrypt('decrypt',$id));
                if(!$patientCareplan->diagnosis->count()) {
                    $validator->errors()->add('careplan_id', trans('validation.diagnosis_not_assigned'));
                }
            }
            else{
                $validator->errors()->add('careplan_id', trans('validation.diagnosis_not_assigned'));  
            }
        });
    }
}
