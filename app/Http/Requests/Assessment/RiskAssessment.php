<?php

namespace App\Http\Requests\Assessment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\PatientCareplan;
use Auth;

class RiskAssessment extends FormRequest
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
		$rules = [
		    'questions.*.value' => 'required',
		    'questions.*.visit' => 'nullable|integer'
        ];

        if($this->has('valid')){
            $data = json_decode($this->get('valid'),true);
            foreach ($data as $key => $value) {
                if(preg_match('/active medication/i', $value)) {
                    $rules['questions.'.$key.'.value'] = 'required|integer';

                }

                if(preg_match('/last month/i', $value)) {
                    $answer = array_get($this->get('questions'),$key.'.value','No');
                    if($answer == 'Yes') {
                        $rules['questions.'.$key.'.visit'] = 'required|integer';
                    }
                }
            }
        }

		return $rules;
    }


     public function messages()
     {
        $message = [];
        if($this->has('valid')){
            $data = json_decode($this->get('valid'),true);
            foreach ($data as $key => $value) {

                if(preg_match('/active medication/i', $value)) {
                    $message['questions.'.$key.'.value.required']= 'Enter number of active medication.';
                    $message['questions.'.$key.'.value.integer']= 'Only numbers are allowed.';
                }else {
                    $message['questions.'.$key.'.value.required']= 'Select Answer.';
                }

            }
        }

        $message['questions.*.visit.integer'] = 'Only numbers are allowed.';
        $message['questions.*.visit.required'] = 'Enter number of visits.';
        return $message;
    }


}
