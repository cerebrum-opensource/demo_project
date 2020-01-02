<?php

namespace App\Http\Requests;

use App\Models\Admin\CarePlan\{ Goal, GoalAssignment };
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class GoalRequest extends FormRequest
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
        if ($this->has('status') && ($this->get('status') == Goal::PARTIALLY_SAVE || $this->get('status') == Goal::DRAFT)) {
             $rules['title'] = "required|max:100|regex:/^[\pL\s\-\. '0-9]+$/u";
        }
        else {
            $rules['title'] = "required|max:1000|regex:/^[\pL\s\-\. '0-9]+$/u";
            $rules['description'] = "required|max:10000";
            $rules['metric_id'] = "required";
            $rules['type'] = 'required|'.Rule::in([Goal::TYPE_QUALITATIVE,Goal::TYPE_QUANTATIVE]).'';
            $rules['flag'] = 'nullable|'.Rule::in([Goal::FLAG_YES,Goal::FLAG_NO]).'';
        }
    	
	    return $rules;
    }

    public function messages(){
    	return [
            'title.required' => 'Enter goal title.',
            'type.required' => 'Select goal type.',
            'description.required' => 'Enter goal description.',
            'title.max' => 'Maximum 1000 characters are allowed.',
            'metric_id.required' => 'Select metric.',
            'description.max' => 'Maximum 10000 characters are allowed.'
        ];
    }


    // Here we can do more with the validation instance...
    public function withValidator($validator)
    {
        $validator->after(function($validator)
        {
            if($this->get('goal_id') && $this->get('status') == Goal::ACTIVE){
                $goalId = encrypt_decrypt('decrypt', $this->get('goal_id'));

                $goal = Goal::find($goalId);
                $goalAssignment = GoalAssignment::where('goal_id', $goalId)
                    ->whereIn('type', ['sub_goal', 'question', 'diagnosis'])
                    ->where('status','!=',GoalAssignment::PARTIALLY_DELETED)
                    ->where('version',$goal->current_version)
                    ->get();
                $types = [];
                if(!$goalAssignment->where('type','sub_goal')->count()) {
                    $types[] = 'sub goal';
                }

                if(!$goalAssignment->where('type','question')->count()) {
                   $types[] = 'question';
                }

                if(!$goalAssignment->where('type','diagnosis')->count()) {
                    $types[] = 'diagnosis';
                }

                if($types) {
                    $types = implode(', ',$types);
                    $search = ',';
                    $replace = ' and';
                    $types =  strrev(implode(strrev($replace), explode(strrev($search), strrev($types), 2)));
                    $types = 'Please add at least one '.$types.'.';

                    $validator->errors()->add('goal_assignment_subgoal_required', $types);
                }
            }
        });
    }
}
