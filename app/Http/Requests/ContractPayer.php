<?php

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContractPayer extends FormRequest
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
        
        $patientId = $this->id ? \Crypt::decrypt($this->id) : null; 

        $rules['name'] = "required|max:100|regex:/^[\pL\s\-\. ']+$/u";
        $rules['organization'] = 'required|max:100';
        $rules['code'] = 'required|max:10';
        $rules['address'] = 'required|max:100';
        $rules['address2'] = 'nullable|max:100';
        $rules['city'] = 'required|max:50';
        $rules['start_date'] = 'required|date_format:m-d-Y';
        $rules['end_date'] = 'required|date_format:m-d-Y';
        $rules['zip'] = 'bail|required|min:5|max:5|regex:/^[0-9]+$/u';
        $rules['state_id'] = 'required|exists:states,id';
        $rules['confirmation'] = 'required|max:100';
        $rules['phone_number'] = 'required|phone';
        $rules['email'] = 'nullable|email|max:45|unique:contract_payers,email,'.$patientId.',id';
        $rules['fax'] = 'nullable|max:10|regex:/^[0-9]+$/u';
       // $rules['web_address'] = 'nullable|max:100|url';
        $rules['web_address'] = ['nullable', 'max:100','regex:/^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/)[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/u'];
        $rules['contact_name'] = "nullable|max:60|regex:/^[\pL\s\-\. ']+$/u";
        $rules['contact_title'] = "nullable|regex:/^[\pL\s\-\. ']+$/u";
        $rules['contact_email'] = 'nullable|email|unique:contract_payers,contact_email,'.$patientId.',id';
        $rules['contact_phone'] = 'nullable|phone';
        $rules['contact_fax'] = 'nullable|max:10|regex:/^[0-9]+$/u';
        return $rules;
    }

    public function messages(){
        return [
            
        ];
    }
}
