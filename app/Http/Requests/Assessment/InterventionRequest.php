<?php

namespace App\Http\Requests\Assessment;

use Illuminate\Foundation\Http\FormRequest;

use App\Models\InterventionFollowup;

class InterventionRequest extends FormRequest
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
            'action' => 'required',
            'summary' => "required|max:1000",
            'assigned_users' => "required",
            'flag' => "required",
        ];
    }


    // Here we can do more with the validation instance...
    public function withValidator($validator)
    {
        $validator->after(function($validator)
        {
            $assessmentId = 0;

            if($this->has('id') && $this->get('id')) {
                $assessmentId = encrypt_decrypt('decrypt', $this->get('id'));
            }

            if($this->has('assessment_id') && $this->get('assessment_id')) {
                $assessmentId = encrypt_decrypt('decrypt', $this->get('assessment_id'));
            }

            $hasFolloups = InterventionFollowup::where([
                'assessment_id' => $assessmentId,
                'type'  => $this->get('type')])
                ->count();

            if(!$hasFolloups) {
                $validator->errors()->add('followup_id', 'Add atleast one follow up item.');
            }

        });
    }


}
