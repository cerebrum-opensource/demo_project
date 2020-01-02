<?php

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BarrierRequest extends FormRequest
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
    	$rules['title'] = "required|max:100|regex:/^[\pL\s\-\. '0-9]+$/u";
        $rules['description'] = "required|max:10000";
        $rules['solution'] = "required|max:10000";
		$rules['category_id'] = "required|exists:manageable_fields,id,type,barrier_category,deleted_at,NULL";
	    return $rules;
    }

    public function messages(){
    	return [
            'description.required' => 'Enter barrier description.',
            'category_id.required' => 'Choose barrier category.',
            'category_id.exists' => 'Barrier category is not valid.',
            'solution.required' => 'Enter suggestions description.',
            'solution.max' => 'Maximum 1000 characters are allowed.',
            'description.max' => 'Maximum 10000 characters are allowed.'
        ];
    }
}
