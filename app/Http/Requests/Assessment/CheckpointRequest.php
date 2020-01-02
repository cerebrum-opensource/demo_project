<?php

namespace App\Http\Requests\Assessment;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\InterventionFollowup;
use App\Models\Intervention;
use App\Models\CareplanCheckpoint;

class CheckpointRequest extends FormRequest
{
    private $tabs = [
        'purpose',
        'content_discussed',
        'intervention'
    ];

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

        ];
    }


     // Here we can do more with the validation instance...
    public function withValidator($validator)
    {
        $validator->after(function($validator)
        {
            if($this->get('checkpoint_id')) {
                $id = encrypt_decrypt('decrypt', $this->get('checkpoint_id'));
                $type = 1;
                $checkpoint = CareplanCheckpoint::findOrFail($id);

                $intervention = Intervention::where(['type_id' => $id,'type'  => $type])->count();
                if((!$intervention)  ){
                    $validator->errors()->add('intervention', 'required');
                }

                $hasInterventionFollowUp = InterventionFollowup::where([
                    'assessment_id' => $id,
                    'type'  => $type
                ])->count();

                if(($checkpoint->visit_content =='' || $checkpoint->other_notes =='')){
                    $validator->errors()->add('content_discussed', 'required');
                }

                if(!$hasInterventionFollowUp){
                    $validator->errors()->add('intervention', 'required');
                }

                if(($checkpoint->purpose =='' || $checkpoint->via =='') && $this->get('tab_name') != 'purpose'){
                    $validator->errors()->add('purpose_tab', 'Please');  
                }
            }
            else {
                foreach ($this->tabs as $tab) {
                    $validator->errors()->add($tab,'required');
                }
            }
        });
    }


}