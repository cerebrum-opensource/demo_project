<?php

namespace App\Http\Requests\Assessment;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\PatientCareplan;


class CheckpointPurposeRequest extends FormRequest
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
        return [
            'purpose' => 'required',
            'via' => 'required'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function($validator)
        {
           
            if(!$this->get('checkpoint_id') &&  $this->get('checkpoint_id') == 0) {
               $patientCareplan = PatientCareplan::where([
                    'patient_id' => encrypt_decrypt('decrypt', $this->get('patient_id')),
                    'status' => PatientCareplan::ACTIVE
                    ])->count();

               if(!$patientCareplan){
                    $validator->errors()->add('careplan_status','Please create a active care plan before creating a checkpoint.');
                }
            }         
            
        });
    }


}
