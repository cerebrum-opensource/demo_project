<?php

namespace App\Http\Requests\Assessment;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\InterventionFollowup;
use App\Models\Intervention;
use App\Models\CareplanCheckpoint;

class AssessmentDiagnosisRequest extends FormRequest
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
        return [
            'diagnosis' => 'required',
        ];
    }


    public function withValidator($validator)
    {
        $validator->after(function($validator)
        {
            $flag = true;
            if($this->get('diagnosis'))
            {
                $diagnosisList = json_decode($this->get('diagnosis'),true);
                foreach ($diagnosisList as  $diagnosis) {
                    $goalids = array_filter(explode(',', $diagnosis['goalids']));
                    if($goalids) {
                        $flag = false;
                    }
                }
            }

            if($flag) {
                $validator->errors()->add('diagnosis','Please select atleast one goal.');
            }
        });
    }

    public function messages()
    {
         return [
             'diagnosis.required' => 'Please select atleast one goal.',
         ];
    }


}
