<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssessmentAddVitalRequest extends FormRequest
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
            'weight' => 'required|numeric|min:1|max:999',
            'a1c' => 'required|numeric|min:1|max:999',
            'blood_pressure_high' => 'required|numeric|min:1|max:999',
            'blood_pressure_low'  => 'required|numeric|min:1|max:999',
            'pulse' => 'required|numeric|min:1|max:999',
        ];
    }

     public function messages()
     {
         return [
             
         ];
     }
}
