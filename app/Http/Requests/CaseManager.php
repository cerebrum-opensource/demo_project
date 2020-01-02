<?php

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;

class CaseManager extends FormRequest
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
     /*  regex:/^[\pL\s\-]+$/u */
     /*  regex:/^[a-zA-Z0-9-_]+$/u */
    	$rules['name'] = "required|max:100|regex:/^[\pL\s\-\. '0-9]+$/u";
		$rules['email'] = 'required|max:45|email|unique:users,email';
        $rules['roles'] = 'required|exists:roles,id';
        $rules['phone'] = 'required|phone|unique:users,phone';
	    return $rules;
    }

    public function messages(){
    	 return [
            'name.regex' => "Only numbers, special characters - ' . and alphabets are allowed."
        ];
    }
}
