<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DiagnosisRequest extends FormRequest
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
        $rules['title'] = "required|max:100|regex:/^[\pL\s\-\. '0-9]+$/u";
        $rules['description'] = 'required|max:10000';
        $rules['metric_id'] = 'required';
        $rules['icd_code'] = 'required|exists:icd_codes,id';
        return $rules;
    }

    public function messages(){
        return [
            'title.required' => 'Enter diagnosis title.',
            'icd_code.required' => 'Enter ICD 10.',
            'title.max' => 'Maximum 100 characters are allowed.',
            'description.required' => 'Enter diagnosis description.',
            'description.max' => 'Maximum 10000 characters are allowed.'
        ];
    }
}
