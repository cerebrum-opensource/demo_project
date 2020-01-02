<?php

namespace App\Http\Requests;


use App\Models\Admin\CarePlan\Tool;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ToolRequest extends FormRequest
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
		$rules['type'] = 'required|'.Rule::in(tool_type()).'';

		if($this->get('type')) {
		    if($this->get('type') === TOOL::TOOL_TYPE_ONLINE) {
                $rules['link'] = 'required|active_url';
            }

          //  if(!$this->get('id')) {
                if($this->get('type') === TOOL::TOOL_TYPE_PDF && !$this->has('is_upload')) {
                    $rules['file_path'] = 'required|mimes:pdf|max:10240';
                }
          //  }


        }

	    return $rules;
    }

    public function messages(){
    	return [
            'title.required' => 'Enter title.',
            'title.max' => 'Maximum 100 characters are allowed.',
            'description.required' => 'Enter tool description.',
            'type.required' => 'Select tool type.',
            'description.max' => 'Maximum 10000 characters are allowed.',

            'link.required' => 'Enter online link.',
            'link.active_url' => 'Enter valid link.',

            'file_path.required' => 'Upload file.',
            'file_path.mimes' => 'Only PDF file allowed.',
        ];
    }
}
