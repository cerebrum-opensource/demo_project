<?php

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class Specialist extends FormRequest
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

          
        $id = $this->speciality_id ? \Crypt::decrypt($this->speciality_id) : null; 
      
        $rules['doctor_name'] = "required|max:60|regex:/^[\pL\s\-\. ']+$/u";
        $rules['org_name'] = 'required|max:100';
        //$rules['email'] = 'nullable|email|max:45';
        $rules['email'] = 'nullable|email|max:45|unique:pcp_informations,email,'.$id.',id,type,specialist';
        $rules['fax'] = 'nullable|max:10|regex:/^[0-9]+$/u';
        $rules['speciality'] = 'required';
        $rules['address_line1'] = 'required|max:100';
        $rules['address_line2'] = 'nullable|max:100';
        $rules['city'] = 'required|max:50';
        $rules['zip_code'] = 'bail|required|min:5|max:5|regex:/^[0-9]+$/u';
        $rules['state_id'] = 'required|exists:states,id';
        $rules['phone'] = 'required|phone';
       // $rules['web_address'] = 'nullable|max:100|url';
        $rules['web_address'] = ['nullable', 'max:100','regex:/^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/)[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/u'];
        $rules['contact_name'] = "nullable|max:60|regex:/^[\pL\s\-\. ']+$/u";
        $rules['contact_title'] = "nullable|regex:/^[\pL\s\-\. ']+$/u";
        $rules['contact_email'] = 'nullable|email|max:45|unique:pcp_informations,contact_email,'.$id.',id,type,specialist';
        $rules['contact_phone'] = 'nullable|phone';
        return $rules;
    }

    public function messages(){
        return [
            
        ];
    }
}
