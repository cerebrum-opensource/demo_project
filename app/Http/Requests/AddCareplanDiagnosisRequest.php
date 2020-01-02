<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddCareplanDiagnosisRequest extends FormRequest
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

        if($this->get('edit_diagnosis_id')) {
            return [
                'goal_ids' => 'required',
            ];
        }

        return [
            'diagnosis_id' => 'required',
            'priority' => 'required|integer|min:1|max:999',
            'goal_ids' => 'required',
        ];
    }

}
