<?php

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PcpInfo extends FormRequest
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
    	$rules['org_name'] = 'required|max:100';
    	$rules['name'] = "required|max:60|regex:/^[\pL\s\-\. '0-9]+$/u";
		$rules['speciality'] = 'required|'.Rule::in(speciality_array()).'';
		$rules['email'] = 'nullable|max:45|email|unique:registries,contact_email,null,id,type,'.$this->type.'';
		$rules['fax'] = 'nullable|max:10|regex:/^[0-9]+$/u';
		$rules['address_line1'] = 'required|max:100';
        $rules['address_line2'] = 'nullable|max:100';


        $rules['contact_email'] = 'nullable|email|max:45|unique:registries,contact_email,null,id,type,'.$this->type.'';
       // $rules['web_address'] = 'nullable|max:100|url';
        $rules['web_address'] = ['nullable', 'max:100','regex:/^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/)[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/u'];
        $rules['contact_name'] = "nullable|max:60|regex:/^[\pL\s\-\. ']+$/u";
        $rules['city'] = 'required|max:50';
        $rules['state_id'] = 'required|exists:states,id';
        $rules['zip'] = 'bail|required|min:5|max:5|regex:/^[0-9]+$/u';
        $rules['contact_phone'] = 'nullable|phone';
        $rules['contact_title'] = "nullable|max:10|regex:/^[\pL\s\-\. ']+$/u";
       // $rules['contact_email'] = 'nullable|max:45|email';
        $rules['phone_number'] = 'required|phone';
        $rules['type'] = 'required|in:pcp_informations,specialities';
	    return $rules;
    }

    public function messages(){
    	 return [
            'name.regex' => "Only numbers, special characters - ' . and alphabets are allowed."
        ];
    }
}
