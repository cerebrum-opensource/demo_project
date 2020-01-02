<?php

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistryRequest extends FormRequest
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
        $type = $this->type ? $this->type : null;

        
        if(in_array($type, ['insurances', 'emergency_departments', 'rehabs', 'hospice_providers', 'housing_assistances', 'mental_health_assistances', 'home_health_providers']))
        {
            $id = $this->id ? $this->decryptId($this->id) : null;

            if($type != 'rehabs' && $type != 'hospice_providers' && $type != 'housing_assistances' && $type != 'mental_health_assistances' && $type != 'home_health_providers' && $type != 'insurances'){
                $rules['name'] = "required|max:100|regex:/^[\pL\s\-\. '0-9]+$/u";
            }else{
                $rules['org_name'] = "required|max:100|regex:/^[\pL\s\-\. '0-9]+$/u";
            }
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
        }
        elseif($type == 'referral_sources')
        {
            $rules['org_name'] = "required|max:100|regex:/^[\pL\s\-\. '0-9]+$/u";
            $rules['code'] = 'nullable|max:10';
            $rules['email'] = 'nullable|max:45|email';
            $rules['fax'] = 'nullable|max:10|regex:/^[0-9]+$/u';
            $rules['address_line1'] = 'required|max:100';
            $rules['address_line2'] = 'nullable|max:100';
           // $rules['web_address'] = 'nullable|max:100|url';
            $rules['web_address'] = ['nullable', 'max:100','regex:/^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/)[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/u'];
            $rules['contact_name'] = "nullable|max:60|regex:/^[\pL\s\-\. ']+$/u";
            $rules['city'] = 'required|max:50';
            $rules['state_id'] = 'required|exists:states,id';
            $rules['zip'] = 'bail|required|min:5|max:5|regex:/^[0-9]+$/u';
            $rules['contact_phone'] = 'nullable|phone';
            $rules['contact_title'] = "nullable|max:10|regex:/^[\pL\s\-\. ']+$/u";
            $rules['contact_email'] = 'nullable|max:45|email';
            $rules['phone_number'] = 'required|phone';
        }
        elseif($type == 'pcp_informations')
        {
            $type = 'pcp';  
            $id = $this->pcp_information_id ? $this->decryptId($this->pcp_information_id) : null;
          
            $rules['name'] = "required|max:60|regex:/^[\pL\s\-\. ']+$/u";
            $rules['org_name'] = 'required|max:100';
            //$rules['email'] = 'nullable|email|max:45';
            $rules['email'] = 'nullable|email|max:45|unique:pcp_informations,email,'.$id.',id,type,pcp';
            $rules['fax'] = 'nullable|max:10|regex:/^[0-9]+$/u';
            $rules['speciality'] = 'required';
            $rules['address_line1'] = 'required|max:100';
            $rules['address_line2'] = 'nullable|max:100';
            $rules['city'] = 'required|max:50';
            $rules['zip'] = 'bail|required|min:5|max:5|regex:/^[0-9]+$/u';
            $rules['state_id'] = 'required|exists:states,id';
            $rules['phone_number'] = 'required|phone';
           // $rules['web_address'] = 'nullable|max:100|url';
            $rules['web_address'] = ['nullable', 'max:100','regex:/^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/)[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/u'];
            $rules['contact_name'] = "nullable|max:60|regex:/^[\pL\s\-\. ']+$/u";
            $rules['contact_title'] = "nullable|regex:/^[\pL\s\-\. ']+$/u";
            //$rules['contact_email'] = 'nullable|email';
           // $rules['contact_email'] = 'nullable|email|max:45|unique:pcp_informations,contact_email,'.$id.',id,type,pcp';
            $rules['contact_email'] = 'nullable|email|max:45|unique:pcp_informations,contact_email,'.$id.',id,type,'.$type.'';
            $rules['contact_phone'] = 'nullable|phone';
        }        
        elseif($type == 'specialities')
        {
            $id = $this->speciality_id ? $this->decryptId($this->speciality_id) : null;
          
            $rules['name'] = "required|max:60|regex:/^[\pL\s\-\. ']+$/u";
            $rules['org_name'] = 'required|max:100';
            //$rules['email'] = 'nullable|email|max:45';
            $rules['email'] = 'nullable|email|max:45|unique:pcp_informations,email,'.$id.',id,type,specialist';
            $rules['fax'] = 'nullable|max:10|regex:/^[0-9]+$/u';
            $rules['speciality'] = 'required';
            $rules['address_line1'] = 'required|max:100';
            $rules['address_line2'] = 'nullable|max:100';
            $rules['city'] = 'required|max:50';
            $rules['zip'] = 'bail|required|min:5|max:5|regex:/^[0-9]+$/u';
            $rules['state_id'] = 'required|exists:states,id';
            $rules['phone_number'] = 'required|phone';
           // $rules['web_address'] = 'nullable|max:100|url';
            $rules['web_address'] = ['nullable', 'max:100','regex:/^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/)[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/u'];
            $rules['contact_name'] = "nullable|max:60|regex:/^[\pL\s\-\. ']+$/u";
            $rules['contact_title'] = "nullable|regex:/^[\pL\s\-\. ']+$/u";
            $rules['contact_email'] = 'nullable|email|max:45|unique:pcp_informations,contact_email,'.$id.',id,type,specialist';
            $rules['contact_phone'] = 'nullable|phone';
        }        
        elseif($type == 'contract_payers')
        {
            $patientId = $this->id ? $this->decryptId($this->id) : null;

            $rules['name'] = "required|max:100|regex:/^[\pL\s\-\. ']+$/u";
            $rules['org_name'] = 'required|max:100';
            $rules['code'] = 'required|max:10';
            $rules['address_line1'] = 'required|max:100';
            $rules['address_line2'] = 'nullable|max:100';
            $rules['city'] = 'required|max:50';
            $rules['effective_start_date'] = 'required|date_format:m-d-Y';
            $rules['effective_end_date'] = 'required|date_format:m-d-Y';
            $rules['zip'] = 'bail|required|min:5|max:5|regex:/^[0-9]+$/u';
            $rules['state_id'] = 'required|exists:states,id';
            $rules['auth_confirmation'] = 'required|max:100';
            $rules['phone_number'] = 'required|phone';
            $rules['email'] = 'nullable|email|max:45|unique:contract_payers,email,'.$patientId.',id';
            $rules['fax'] = 'nullable|max:10|regex:/^[0-9]+$/u';
           // $rules['web_address'] = 'nullable|max:100|url';
            $rules['web_address'] = ['nullable', 'max:100','regex:/^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/)[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/u'];
            $rules['contact_name'] = "nullable|max:60|regex:/^[\pL\s\-\. ']+$/u";
            $rules['contact_title'] = "nullable|regex:/^[\pL\s\-\. ']+$/u";
            $rules['contact_email'] = 'nullable|email|unique:contract_payers,contact_email,'.$patientId.',id';
            $rules['contact_phone'] = 'nullable';
            $rules['contact_fax'] = 'nullable|max:10|regex:/^[0-9]+$/u';
        }

        return $rules;
    }

    private function decryptId($id)
    {
        return encrypt_decrypt('decrypt', $id);
    }
}
