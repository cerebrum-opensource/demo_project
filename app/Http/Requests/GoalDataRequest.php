<?php

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Admin\CarePlan\{ Goal };

class GoalDataRequest extends FormRequest
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
        if ($this->has('save_type') && $this->get('save_type') == Goal::SUB_GOALS) {
             $rules['description'] = "required|max:1000";
             $rules['assigned_roles'] = 'required|exists:roles,id';
        }
        if ($this->has('save_type') && $this->get('save_type') == Goal::QUESTIONS) {
             $rules['description'] = "required|max:1000";
             $rules['metric_id'] = 'required';
             $rules['assigned_roles'] = 'required|exists:roles,id';
        }  
        if ($this->has('save_type') && $this->get('save_type') == Goal::BARRIERS) {
             $rules['barrier_id'] = "required|max:1000";
        }  
        if ($this->has('save_type') && $this->get('save_type') == Goal::TOOLS) {
             $rules['tool_id'] = "required|max:1000";
        }
        if ($this->has('save_type') && $this->get('save_type') == Goal::DIAGNOSIS) {
             $rules['diagnosis_id'] = "required|max:1000";
        }

	    return $rules;
    }

    public function messages()
    {
        if ($this->has('save_type') && $this->get('save_type') == Goal::QUESTIONS) {
            return [
               'metric_id.required' => 'Select metric.',
               'description.required' => 'Enter question.',
               'description.max' => 'Maximum 1000 characters are allowed.',
               'assigned_roles.required' => 'Select role.'
            ];
        } else if($this->has('save_type') && $this->get('save_type') == Goal::SUB_GOALS) {
            return [
                'description.max'      => 'Maximum 1000 characters are allowed.',
                'description.required' => 'Enter sub goal description.',
                'assigned_roles.required' => 'Select role.',
            ];
        } else if ($this->has('save_type') && $this->get('save_type') == Goal::TOOLS) {
            return [
                'tool_id.required' => 'Please add tool(s).'
            ];
        } else if ($this->has('save_type') && $this->get('save_type') == Goal::BARRIERS) {
            return [
                'name.required' => 'Enter barrier description.',
                'name.max' => 'Maximum 10000 characters are allowed.',
                'solution.required' => 'Enter solution description.',
                'solution.max' => 'Maximum 10000 characters are allowed.',

            ];
        } else if ($this->has('save_type') && $this->get('save_type') == Goal::DIAGNOSIS) {
            return [
                'diagnosis_id.required' => 'Please add diagnosis.'
            ];
        }
        else {
            return [];
        }
    }
}
