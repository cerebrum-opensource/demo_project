<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CareplanStatusRequest extends FormRequest
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
        if ($this->has('is_base_line') && $this->get('is_base_line') == '1') {

        }
        else{
            $rules['reason'] = 'required'; 
        }
       
        
        return $rules;
    }

    public function messages(){
        return [
            'reason.required' => "Enter reason."
        ];
    }
}
