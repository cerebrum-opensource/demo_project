<?php

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;

class ManageAbleField extends FormRequest
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
      
        $rules['name'] = 'required|max:100';
        $rules['type'] = 'required';
        return $rules;
    }

    public function messages(){
        return [
            
        ];
    }
}
