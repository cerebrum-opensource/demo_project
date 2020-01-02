<?php

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistryType1 extends FormRequest
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
      
        $type = $this->type ? $this->type : null;
        $id = $this->id ? \Crypt::decrypt($this->id) : null; 
        $rules['name'] = "required|max:100|regex:/^[\pL\s\-\. ']+$/u";
        $rules['address_line1'] = 'required|max:100';
        $rules['address_line2'] = 'nullable|max:100';
        $rules['city'] = 'required|max:50';
        $rules['zip'] = 'bail|required|min:5|max:5|regex:/^[0-9]+$/u';
        $rules['state_id'] = 'required|exists:states,id';
        $rules['phone_number'] = 'required|phone';
        $rules['type'] = Rule::in(registry_type1());
        $rules['fax'] = 'nullable|max:10|regex:/^[0-9]+$/u';
       // $rules['web_address'] = 'nullable|max:100|url';
        $rules['web_address'] = ['nullable', 'max:100','regex:/^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/)[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/u'];
        $rules['contact_name'] = "nullable|max:60|regex:/^[\pL\s\-\. ']+$/u";
        $rules['contact_title'] = "nullable|regex:/^[\pL\s\-\. ']+$/u";
        $rules['contact_email'] = 'nullable|email|max:45|unique:registries,contact_email,'.$id.',id,type,'.$type.'';
        $rules['contact_phone'] = 'nullable|phone';
        if($type == 'emergency_departments'){
            $typeEmail = 'emergency_departments';
            $rules['email'] = 'nullable|email|max:45|unique:registries,email,'.$id.',id,type,'.$typeEmail.'';
            $rules['code'] = 'nullable|max:100';
        }
        if($type == 'insurances'){
             $rules['code'] = 'nullable|max:100';
        }
        
        
     //   $rules['contact_email'] = 'nullable|email';
       
        return $rules;
    }

    public function messages(){
        return [
            
        ];
    }
}
