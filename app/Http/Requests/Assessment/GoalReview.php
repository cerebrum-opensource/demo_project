<?php

namespace App\Http\Requests\Assessment;

use Illuminate\Foundation\Http\FormRequest;


class GoalReview extends FormRequest
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
            'diagnosis.*.*.flag' => 'required',
            'diagnosis.*.*.summary' => 'required',
            'diagnosis.*.*.metric' => 'required',
           // 'diagnosis.*.*.goal.*.*.answer' => 'required'
        ];
    }

    public function messages()
    {
        return [
                'diagnosis.*.*.flag.required' => 'Select flag for diagnosis.',
                'diagnosis.*.*.summary.required' => 'Enter summary.',
                'diagnosis.*.*.metric.required' => 'Select metric for diagnosis.',
        ];
    }


     // Here we can do more with the validation instance...

     public function withValidator($validator)
    {
        $validator->after(function($validator)
        {
            $array = [];
            if($this->get('diagnosis')) {
                    foreach ($this->get('diagnosis') as $key =>  $diagnosis) {
                        # code...
                        foreach ($diagnosis as $keyVersion => $diagnosisDetail) {
                            if(isset($diagnosisDetail['goal'])){
                                
                            
                            foreach ($diagnosisDetail['goal'] as  $goals) {
                                $isError = 1;
                                foreach ($goals as  $goal) {
                                if($isError){
                                   if(isset($goal['answer']) && $goal['answer'] != ''){
                                    $isError = 0;
                                        
                                   }
                                   else{
                                    $isError = 1; 
                                   }
                                }
                                }

                                if($isError){
                                     $isError = 1; 
                                     $validator->errors()->add('dia_'.$key, 'Anwser atleast one question for each goal');  
                                }   

                            }

                            }
                            else{
                               $validator->errors()->add('dia_'.$key, 'There are no question for this goal. please choose another one.');   
                            }
                        }
                    }
            }
            else {
                $validator->errors()->add('purpose_tab', 'Please');  
            }
        });
    }
    

}
