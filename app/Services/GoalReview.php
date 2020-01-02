<?php
/**
 * Created by PhpStorm.
 * User: gopal
 * Date: 11/4/19
 * Time: 3:09 PM
 */

namespace App\Services;
use App\Models\{Admin\CarePlan\GoalAssignment,
    CareplanAssessment,
    CareplanDiagnosis,
    CareplanGoal,
    CareplanAssessmentGoalReview,
    CareplanAssessmentGoalReviewData,
    AssessmentGoal,
    Admin\CarePlan\GoalVersion};
use DB;



class GoalReview
{
    /**
     * Get Diagnosis List By Assessment Id
     * @param $assessmentId
     * @return mixed
     */
    public function getDiagnosisList($assessmentId)
    {
        $assessment = CareplanAssessment::findOrFail($assessmentId);
        return CareplanDiagnosis::select('dv.diagnosis_id', 'dv.version', 'dv.code', 'dv.title', 'dv.description', 'careplan_diagnosis.priority', 'careplan_diagnosis.goal_count')
            ->join('diagnosis_versions as dv', function ($join) {
                $join->on('dv.diagnosis_id', '=', 'careplan_diagnosis.diagnosis_id');
                $join->on('dv.version', '=', 'careplan_diagnosis.diagnosis_version');
            })
            ->where('careplan_diagnosis.careplan_id', $assessment->careplan_id)
            ->where('careplan_diagnosis.status', '1')
            ->where('careplan_diagnosis.deleted_at', null)
            ->where('dv.status', '1')
            ->where('dv.deleted_at', null)
            ->paginate(CASELOAD_PAGINATION_COUNT);
    }

    /**
     * Delete Diagnosis
     * @param $request
     * @return bool
     */
    public function deleteDiagnosis($request)
    {
        $assessmentId = encrypt_decrypt('decrypt', $request->get('assessment_id'));
        $diagnosisId = encrypt_decrypt('decrypt', $request->get('diagnosis_id'));
        $diagnosisVersion = $request->get('diagnosis_version');

        $assessmentGoalReview = CareplanAssessmentGoalReview::where([
            'assessment_id' => $assessmentId,
            'diagnosis_id' => $diagnosisId,
            'diagnosis_version' => $diagnosisVersion,
        ])->first();

        if($assessmentGoalReview) {
            AssessmentGoal::where([
                'assessment_id' => $assessmentId,
                'diagnosis_id' => $diagnosisId,
            ])->delete();

            $assessmentGoalReview->getGoalReviewData()->delete();
            $assessmentGoalReview->delete();
        }

        return true;

    }


    /**
     * Add or update diagnosis and its goals to goal review, add to assessment
     * @param $request
     * @return bool
     */
    public function addOrUpdate($request)
    {
        $assessmentId = encrypt_decrypt('decrypt', $request->get('assessment_id'));
        $patientId = encrypt_decrypt('decrypt', $request->get('patient_id'));

        $goalAssessmentIds = [];
        $goalAssessmentReviewIds = [];


        if ($request->has('diagnosis') && $request->get('diagnosis')) {

            $diagnosisList = json_decode($request->get('diagnosis'), true);
            foreach ($diagnosisList as $diagnosis) {

                $diagnosisId = encrypt_decrypt('decrypt', $diagnosis['diagnosis_id']);
                $diagnosisVersion = $diagnosis['diagnosis_version'];
                $goalids = array_filter(explode(',', $diagnosis['goalids']));

                $goalReview['assessment_id'] = $assessmentId;
                $goal['assessment_id'] = $assessmentId;
                $goalReview['patient_id'] = $patientId;
                $goalReview['diagnosis_id'] = $diagnosisId;
                $goal['diagnosis_id'] = $diagnosisId;
                $goal['status'] = AssessmentGoal::DRAFT;
                $goal['added_date'] = now();
                $goal['patient_id'] = $patientId;
                $goalReview['diagnosis_version'] = $diagnosisVersion;
                $goalReview['status'] = CareplanAssessmentGoalReview::DRAFT;;

                $goalAssessment = '';
                foreach ($goalids as $goalId) {
                    $goalData = explode('-', $goalId);
                    $goal['goal_id'] = encrypt_decrypt('decrypt', $goalData[0]);
                    $goal['goal_version'] = $goalData[1];

                    $goalAssessment = AssessmentGoal::updateOrCreate([
                        'assessment_id' => $assessmentId,
                        'goal_id' => $goal['goal_id'],
                        'goal_version' => $goal['goal_version'],
                        'diagnosis_id' => $diagnosisId
                    ], $goal);

                    $goalAssessmentIds[] = $goalAssessment->id;
                }

                if($goalAssessment) {
                    $assessmentGoalReview = CareplanAssessmentGoalReview::updateOrCreate([
                        'assessment_id' => $assessmentId,
                        'diagnosis_id' => $diagnosisId,
                        'diagnosis_version' => $diagnosisVersion,
                    ],$goalReview);

                    $goalAssessmentReviewIds[] = $assessmentGoalReview->id;
                }


            }
        }

        /* for update diagnosis goal */
        AssessmentGoal::whereNotIn('id', $goalAssessmentIds)->where('assessment_id', $assessmentId)->delete();
        $assessmentGoalReviews = CareplanAssessmentGoalReview::whereNotIn('id', $goalAssessmentReviewIds)->where('assessment_id', $assessmentId)->get();
        if($assessmentGoalReviews->count()) {
            foreach($assessmentGoalReviews as $assessmentGoalReview) {
                if($assessmentGoalReview->getGoalReviewData->count()) {                    
                    $assessmentGoalReview->getGoalReviewData->delete();
                }
                $assessmentGoalReview->delete();
            }
        }

        return CareplanAssessment::findOrFail($assessmentId);
    }

    /**
     * Get Goal Reviews by assessment Id
     * @param $assessmentId
     * @return mixed
     */
    public function getGoalReviewByAssessmentId($assessmentId)
    {
        return CareplanAssessmentGoalReview::select('assessment_id', 'patient_id', 'summary', 'flag_id', 'metric_value', 'assessment_goal_review.diagnosis_id', 'title', 'diagnosis_version', 'dv.metric_id')->join('diagnosis_versions as dv', function ($join) {
            $join->on('dv.diagnosis_id', '=', 'assessment_goal_review.diagnosis_id');
            $join->on('dv.version', '=', 'assessment_goal_review.diagnosis_version');
        })->where('assessment_id', $assessmentId)->with('metric')->get();
    }

    /**
     * Get Goal Review Data by assessment Id
     * @param $assessmentId
     * @return mixed
     */
    public function getGoalReviewDataByAssessmentId($assessmentId)
    {
        return CareplanAssessmentGoalReview::select('diagnosis_id', 'diagnosis_version', 'dv.*')->join('assessment_goal_review_data as dv', function ($join) {
            $join->on('dv.assessment_goal_review_id', '=', 'assessment_goal_review.id');
        })->where('assessment_goal_review.assessment_id', $assessmentId)->get();
    }

    /**
     * Delete assessment diagnosis goal
     * @param $request
     * @return bool
     */
    public function deleteAssessmentDiagnosisGoal($request)
    {
        try {
            $diagnosisId = encrypt_decrypt('decrypt', $request->get('diagnosisId'));
            $goalId = encrypt_decrypt('decrypt', $request->get('goalId'));
            $assessmentId = encrypt_decrypt('decrypt', $request->get('assessmentId'));
        } catch (\Exception $e) {
            abort(404);
        }
        DB::beginTransaction();
        $isDeleteDiagnosis = AssessmentGoal::where(['assessment_id' => $assessmentId, 'diagnosis_id' => $diagnosisId, 'goal_id' => $goalId])->delete();
        $isDeleteGoal = CareplanAssessmentGoalReviewData::where(['assessment_id' => $assessmentId, 'goal_id' => $goalId])->delete();
        if ($isDeleteDiagnosis || $isDeleteGoal) {
            DB::commit();
            return true;
        } else {
            DB::rollBack();
            return false;
        }
    }

    /**
     * Add the goal and its question answers
     * @param $request
     * @return bool
     */
    public function addItemAnswers($request)
    {
        $careplanId = encrypt_decrypt('decrypt', $request->get('careplan_id'));
        $assessmentId = encrypt_decrypt('decrypt', $request->get('assessment_id'));
        $patientId = encrypt_decrypt('decrypt', $request->get('patient_id'));

        $goalReviewData = [];
        $goalReviewData1 = [];
        $isSave = false;
        $isSaveGoal = false;
        if ($request->has('diagnosis')) {
            CareplanAssessmentGoalReviewData::where('assessment_id', $assessmentId)->where('status', CareplanAssessmentGoalReviewData::DRAFT)->where('item_type', 'goal')->delete();
            foreach ($request->get('diagnosis') as $key => $diagnosis) {
                foreach ($diagnosis as $keyVersion => $diagnosisDetail) {

                    $UpdatedReview = CareplanAssessmentGoalReview::where(['assessment_id' => $assessmentId, 'patient_id' => $patientId, 'diagnosis_id' => $key, 'diagnosis_version' => $keyVersion])->update([
                        'metric_id' => $diagnosisDetail['metric_id'],
                        'metric_value' => $diagnosisDetail['metric'],
                        'summary' => $diagnosisDetail['summary'],
                        'flag_id' => $diagnosisDetail['flag']
                    ]);
                    if ($UpdatedReview) {
                        $isSave = true;
                    }
                    $UpdatedReviewData = CareplanAssessmentGoalReview::where(['assessment_id' => $assessmentId, 'patient_id' => $patientId, 'diagnosis_id' => $key, 'diagnosis_version' => $keyVersion])->first();
                    foreach ($diagnosisDetail['goal'] as $goalId => $goals) {
                        foreach ($goals as $questionId => $question) {
                            $goal['assessment_id'] = $assessmentId;
                            $goal['patient_id'] = $patientId;
                            $goal['status'] = AssessmentGoal::DRAFT;
                            $goal['added_date'] = now();
                            $goal['goal_id'] = $goalId;
                            $goal['item_type_id'] = $goalId;
                            $goal['question_id'] = $questionId;
                            $goal['metric_id'] = $question['metric_id'];
                            $goal['item_type'] = 'goal';
                            $goal['added_by'] = auth()->user()->id;
                            $goal['assessment_goal_review_id'] = $UpdatedReviewData->id;
                            if (isset($question['flag'])) {
                                $goal['flag_id'] = $question['flag'];
                            }
                            if (isset($question['answer'])) {
                                $goal['answer'] = $question['answer'];
                            }
                            if (CareplanAssessmentGoalReviewData::create($goal)) {
                                $isSave = true;

                            }
                        }
                    }

                }
            }

            if (!$isSave) {
                DB::rollBack();
                return false;
            }

            DB::commit();
            $assessment = CareplanAssessment::findOrFail($assessmentId);
            return $assessment;
        }
    }
}