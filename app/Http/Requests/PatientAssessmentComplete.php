<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\PatientAssessment;
use Auth;

class PatientAssessmentComplete extends FormRequest
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
                 $patient_id = \Crypt::decrypt($this->get('patient_id'));
                 $count = PatientAssessment::where('comment_type','!=','consent_rejection')->where(['patient_id' =>$patient_id,'type_id' => $user_id])->count();
                 if($count < 1){
                    $validator->errors()->add('notes_error', trans('message.not_compelte_assesment'));
                 }    
            }
        });
    }

   
}
