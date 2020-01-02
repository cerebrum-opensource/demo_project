<?php

namespace App\Http\Requests\Assessment;

use App\Models\CareplanAssessment;
use App\Models\CareplanAssessmentGoalReviewData;
use App\Models\Intervention;
use App\Models\InterventionFollowup;
use Illuminate\Foundation\Http\FormRequest;

class AssessmentCreateRequest extends FormRequest
{
    private $tabs = [
        'purpose',
        'goal_review',
        'priority_alignment',
        'risk_assessment',
        'intervention',
        'progress_notes',
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
            //
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function($validator)
        {
            if($this->get('assessment_id')) {
                $assessmentId = encrypt_decrypt('decrypt', $this->get('assessment_id'));
                $patientId = encrypt_decrypt('decrypt', $this->get('patient_id'));

                $assessment = CareplanAssessment::findOrFail($assessmentId);

                $hasPriorityAlignment = CareplanAssessmentGoalReviewData::where([
                    'assessment_id' => $assessmentId,
                    'patient_id' => $patientId,
                    'item_type' => 'priority_alignment'
                ])->count();

                if(!$hasPriorityAlignment) {
                    $validator->errors()->add('priority_alignment','required');
                }

                $hasRiskAssessment = CareplanAssessmentGoalReviewData::where([
                    'assessment_id' => $assessmentId,
                    'patient_id' => $patientId,
                    'item_type' => 'risk_assessment'
                ])->count();

                if(!$hasRiskAssessment) {
                    $validator->errors()->add('risk_assessment','required');
                }

                $hasIntervention = Intervention::where([
                    'type_id' => $assessmentId,
                    'type' => Intervention::TYPE_ASSESSMENT
                ])->count();

                if(!$hasIntervention) {
                    $validator->errors()->add('intervention','required');
                }

                $hasfollowUps = InterventionFollowup::where([
                    'assessment_id' => $assessmentId,
                    'type' => Intervention::TYPE_ASSESSMENT
                ])->count();

                if(!$hasfollowUps) {
                    $validator->errors()->add('intervention','required');
                }

                if(!($assessment->overall_notes)) {
                    $validator->errors()->add('progress_notes','required');
                }

                $hasGoalReviewData = CareplanAssessmentGoalReviewData::where([
                    'assessment_id' => $assessmentId,
                    'patient_id' => $patientId,
                    'item_type' => 'goal'
                ])->count();

                if (!$hasGoalReviewData) {
                    $validator->errors()->add('goal_review','required');
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
