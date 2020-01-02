<?php


namespace App\Services;

use App\Models\{InterventionFollowup,
    Vital,
    Admin\CarePlan\Barrier,
    Admin\CarePlan\Question,
    AssessmentBarrier,
    CareplanAssessment,
    Intervention,
    CareplanAssessmentGoalReviewData,
    CareplanAssessmentGoalReview,
    PatientCareplan,
    AssessmentGoal};

use Auth;
use Carbon\Carbon;
use CareplanAssessments;
use Spatie\Permission\Models\Role;

class Assessment
{
    /**
     * Get Assessment By Id
     * @param $assessmentId
     * @return mixed
     */
    public function getById($assessmentId)
    {
        return CareplanAssessment::findOrFail($assessmentId);
    }

    /**
     * Add or Create assessment with active status
     * @param $request
     * @return mixed
     */
    public function add($request)
    {
        $assessmentId = encrypt_decrypt('decrypt', $request->get('assessment_id'));
        Vital::where('assessment_id', $assessmentId)->update([
            'status' => Vital::ACTIVE
        ]);
        AssessmentBarrier::where('assessment_id', $assessmentId)->update([
            'status' => AssessmentBarrier::ACTIVE
        ]);
        CareplanAssessmentGoalReviewData::where('assessment_id', $assessmentId)->update([
            'status' => CareplanAssessmentGoalReviewData::ACTIVE
        ]);
        CareplanAssessmentGoalReview::where('assessment_id', $assessmentId)->update([
            'status' => CareplanAssessmentGoalReview::ACTIVE
        ]);
        AssessmentGoal::where('assessment_id', $assessmentId)->update([
            'status' => AssessmentGoal::ACTIVE
        ]);
        return CareplanAssessment::where('id', $assessmentId)->update([
            'status' => CareplanAssessment::ACTIVE
        ]);
    }

    /**
     * Copy assessment by assessment id with draft status
     * @param $assessmentId
     * @return mixed
     */
    public function copy($assessmentId)
    {
        $assessment = CareplanAssessment::find($assessmentId);

        $roleId = 0;
        if (auth()->user()->hasAnyRole(Role::all())) {
            $roleId = auth()->user()->roles->pluck('id')[0];
        }

        $clonedAssessment = CareplanAssessment::create([
            'careplan_id' => $assessment->careplan_id,
            'patient_id' => $assessment->patient_id,
            'assessment_by' => auth()->user()->id,
            'assessment_date' => now(),
            'purpose' => $assessment->purpose,
            'via' => $assessment->via,
            'user_type' => $roleId,
            'code' => $this->generateAssessmentID($assessment->patient_id),
        ]);


//        if ($intervention = $assessment->intervention) {
//            $intervention = $intervention->replicate();
//            $intervention->type_id = $clonedAssessment->id;
//            $intervention->type = Intervention::TYPE_ASSESSMENT;
//            $intervention->added_date = today()->format('Y-m-d');
//            $intervention->save();
//        }

        foreach ($assessment->interventionFollowups as $followup) {
            $clonedFollowup = $followup->replicate();
            $clonedFollowup->assessment_id = $clonedAssessment->id;
            $clonedFollowup->added_date = today()->format('Y-m-d');
            $clonedFollowup->added_by = auth()->user()->id;
            $clonedFollowup->user_type = $roleId;
            $clonedFollowup->status = InterventionFollowup::ACTIVE;
            $clonedFollowup->save();
        }

        foreach ($assessment->getGoalReview as $goalReview) {
            $clonedGoalReview = $goalReview->replicate();
            $clonedGoalReview->summary = '';
            $clonedGoalReview->flag_id = 0;
            $clonedGoalReview->metric_value = null;
            $clonedGoalReview->assessment_id = $clonedAssessment->id;
            $clonedGoalReview->status = CareplanAssessmentGoalReview::DRAFT;
            $clonedGoalReview->save();
        }

        foreach ($assessment->getAssessmentGoals as $assessmentGoal) {
            $clonedAssessmentGoal = $assessmentGoal->replicate();
            $clonedAssessmentGoal->assessment_id = $clonedAssessment->id;
            $clonedAssessmentGoal->status = AssessmentGoal::DRAFT;
            $clonedAssessmentGoal->save();
        }

        return $clonedAssessment;
    }

    /**
     * Add purpose
     * @param $request
     * @return mixed
     */
    public function addPurpose($request)
    {
        $patientId = encrypt_decrypt('decrypt', $request->get('patient_id'));
        $patientCareplan = PatientCareplan::where([
            'patient_id' => $patientId,
            'status' => PatientCareplan::ACTIVE
        ])->first();

        $roleId = 0;
        if (auth()->user()->hasAnyRole(Role::all())) {
            $roleId = auth()->user()->roles->pluck('id')[0];
        }
        return CareplanAssessment::create([
            'careplan_id' => $patientCareplan->id,
            'patient_id' => $patientId,
            'purpose' => $request->get('purpose'),
            'assessment_by' => auth()->user()->id,
            'assessment_date' => now(),
            'user_type' => $roleId,
            'via' => $request->get('via'),
            'code' => $this->generateAssessmentID($patientId),
        ]);
    }

    /**
     * Update Purpose
     * @param $request
     * @return mixed
     */
    public function updatePurpose($request)
    {
        $assessmentId = encrypt_decrypt('decrypt', $request->get('assessment_id'));
        $careplanAssessment = CareplanAssessment::findOrFail($assessmentId);

        $careplanAssessment->fill([
            'purpose' => $request->get('purpose'),
            'via' => $request->get('via')
        ])->save();

        return $careplanAssessment;

    }

    /**
     * Get Risk Assessment Data by Patient and Assessment Id
     * @param $patientId
     * @param int $assessmentId
     * @return mixed
     */
    public function getRiskAssessment($patientId, $assessmentId = 0)
    {
        $data['questions'] = Question::where(['question_type' => Question::RISK_ASSESSMENT, 'status' => Question::ACTIVE])
            ->with('metric')
            ->with(['goalReviewData' => function ($query) use ($patientId, $assessmentId) {
                $query->where([
                    'item_type' => 'risk_assessment',
                    'patient_id' => $patientId,
                    'assessment_id' => $assessmentId,
                ]);
            }])->get();
        $data['validation'] = Question::where(['question_type' => Question::RISK_ASSESSMENT, 'status' => Question::ACTIVE])->with('metric')->pluck('description', 'id');

        return $data;
    }

    /**
     * Get Priority Alignment by patient or assessment Id
     * @param $patientId
     * @param int $assessmentId
     * @return mixed
     */
    public function getPriorityAlignment($patientId, $assessmentId = 0)
    {
        $data['questions'] = Question::where(['question_type' => Question::PRIORITY_ALIGNMENT, 'status' => Question::ACTIVE])
            ->with('metric')
            ->with(['goalReviewData' => function ($query) use ($patientId, $assessmentId) {
                $query->where([
                    'item_type' => 'priority_alignment',
                    'patient_id' => $patientId,
                    'assessment_id' => $assessmentId,
                ]);
            }])->get();
        $data['validation'] = Question::where(['question_type' => Question::PRIORITY_ALIGNMENT, 'status' => Question::ACTIVE])->with('metric')->pluck('description', 'id');

        return $data;
    }

    /**
     * Search Barrier to add on list
     * @param $request
     * @return mixed
     */
    public function searchBarrier($request)
    {
        $assessmentId = encrypt_decrypt('decrypt', $request->get('assessment_id'));
        $addedBarrierIds = AssessmentBarrier::where('assessment_id', $assessmentId)->get()->pluck('barrier_id')->toArray();
        return Barrier::where(function ($query) use ($request) {
            $query->orWhere('code', 'like', $request->get('query') . '%')
                ->orWhere('title', 'like', '%' . $request->get('query') . '%');
        })->where('status', Barrier::ACTIVE)->whereNotIn('id', $addedBarrierIds)->get();
    }

    /**
     * Add Barrier
     * @param $formData
     * @return bool
     */
    public function addBarrier($formData)
    {
        $assessmentBarrier = AssessmentBarrier::where($formData)->first();
        if (!$assessmentBarrier) {
            return AssessmentBarrier::create($formData);
        }

        return true;
    }

    /**
     * Get Barrier list by assessmentId id
     * @param $assessmentId
     * @return mixed
     */
    public function getBarrierList($assessmentId)
    {
        return AssessmentBarrier::where([
            'assessment_id' => $assessmentId,
        ])->with('barrier')->paginate(CASELOAD_PAGINATION_COUNT)
            ->withPath(url('patients/assessment/barriers/list?assessment_id=' . encrypt_decrypt('encrypt', $assessmentId)));
    }

    /**
     * Delete Barrier by assessment Barrier Id
     * @param $assessmentBarrierId
     * @return bool
     */
    public function deleteBarrier($assessmentBarrierId)
    {
        AssessmentBarrier::destroy($assessmentBarrierId);
        return true;
    }

    /**
     * Get Barrier detail by Id
     * @param $barrierId
     * @return mixed
     */
    public function getBarrierById($barrierId)
    {
        return Barrier::findOrFail($barrierId);
    }

    /**
     * Add Vital
     * @param $request
     * @param $assessmentId
     * @return mixed
     */
    public function addVital($request, $assessmentId)
    {
        $formData = $request->except(['_token', 'assessment_id']);
        $formData['careplan_id'] = encrypt_decrypt('decrypt', $request->get('careplan_id'));
        $formData['patient_id'] = encrypt_decrypt('decrypt', $request->get('patient_id'));
        $formData['assessment_id'] = $assessmentId;
        return Vital::create($formData);
    }

    /**
     * Get Vital List By assessment Id
     * @param $assessmentId
     * @return mixed
     */
    public function getVitalList($assessmentId)
    {
        return Vital::where('assessment_id', $assessmentId)
            ->paginate(CASELOAD_PAGINATION_COUNT)
            ->withPath(url('patients/assessment/vitals/list?assessment_id=' . encrypt_decrypt('encrypt', $assessmentId)));
    }

    /**
     * Save Progress notes and Content Discussed
     * @param $formData
     * @param $assessmentId
     * @return bool
     */
    public function updateAssessment($formData, $assessmentId)
    {
        $assessment = CareplanAssessment::findOrFail($assessmentId);
        $assessment->fill($formData)->save();
        return $assessment;
    }

    /**
     * Add Risk assessment and priority alignment question answers
     * @param $request
     * @return bool
     */
    public function addItemsAnswers($request)
    {
        $patientId = encrypt_decrypt('decrypt', $request->get('patient_id'));
        $assessmentId = encrypt_decrypt('decrypt', $request->get('assessment_id'));

        CareplanAssessmentGoalReviewData::where([
            'assessment_id' => $assessmentId,
            'patient_id' => $patientId,
            'item_type' => $request->get('item_type')
        ])->delete();

        $questions = $request->get('questions');
        $answerList = [];
        foreach ($questions as $key => $question) {
            $answer = [];
            $answer['patient_id'] = $patientId;
            $answer['item_type'] = $request->get('item_type');
            $answer['assessment_id'] = $assessmentId;
            $answer['question_id'] = $key;
            $answer['added_date'] = now();
            $answer['added_by'] = auth()->user()->id;
            $answer['answer'] = $question['value'];
            $answer['created_at'] = now();
            $answer['updated_at'] = now();

            $answer['no_of_visits'] = 0;
            if (isset($question['visit'])) {
                $answer['no_of_visits'] = $question['visit'];
            }
            $answerList[] = $answer;
        }

        CareplanAssessmentGoalReviewData::insert($answerList);

        return true;
    }


    /**
     * Get Barrier list for care plan
     * @param $careplanId
     * @param $patientId
     * @return mixed
     */
    public function getBarrierListForCareplan($careplanId, $patientId)
    {
        $careplanId = encrypt_decrypt('decrypt', $careplanId);
        $patientId = encrypt_decrypt('decrypt', $patientId);

        return AssessmentBarrier::where([
            'careplan_id' => $careplanId,
            'patient_id' => $patientId,
            'status' => AssessmentBarrier::ACTIVE,
        ])->with('barrier')->paginate(CASELOAD_PAGINATION_COUNT)
            ->withPath(url('patients/careplan/barriers/list?careplan_id=' . encrypt_decrypt('encrypt', $careplanId)) . '&patient_id=' . encrypt_decrypt('encrypt', $patientId));
    }


    /**
     * Get Vital List for care plan by careplan or patientId
     * @param $careplanId
     * @param $patientId
     * @return mixed
     */
    public function getVitalListForCareplan($careplanId, $patientId)
    {
        $careplanId = encrypt_decrypt('decrypt', $careplanId);
        $patientId = encrypt_decrypt('decrypt', $patientId);

        return Vital::where([
            'careplan_id' => $careplanId,
            'patient_id' => $patientId,
            'status' => Vital::ACTIVE,
        ])->paginate(CASELOAD_PAGINATION_COUNT)
            ->withPath(url('patients/careplan/vitals/list?careplan_id=' . encrypt_decrypt('encrypt', $careplanId)) . '&patient_id=' . encrypt_decrypt('encrypt', $patientId));
    }

    /**
     * Delete Partially saved assessment
     */
    public function deletePartialAssessments()
    {
        $assessmentIds = CareplanAssessment::where(['status' => CareplanAssessment::DRAFT, 'assessment_by' => auth()->user()->id])->pluck('id')->toArray();
        InterventionFollowup::whereIn('assessment_id', $assessmentIds)->where('type', InterventionFollowup::TYPE_ASSESSMENT)->delete();
        Intervention::whereIn('type_id', $assessmentIds)->where('type', Intervention::TYPE_ASSESSMENT)->delete();
        CareplanAssessment::where(['status' => CareplanAssessment::DRAFT, 'assessment_by' => auth()->user()->id,])->delete();
        Vital::whereIn('assessment_id', $assessmentIds)->where('status', Vital::DRAFT)->delete();
        AssessmentBarrier::whereIn('assessment_id', $assessmentIds)->where('status', AssessmentBarrier::DRAFT)->delete();
        CareplanAssessmentGoalReviewData::whereIn('assessment_id', $assessmentIds)->where('status', CareplanAssessmentGoalReviewData::DRAFT)->delete();
        CareplanAssessmentGoalReview::whereIn('assessment_id', $assessmentIds)->where('status', CareplanAssessmentGoalReview::DRAFT)->delete();
        AssessmentGoal::whereIn('assessment_id', $assessmentIds)->where('status', AssessmentGoal::DRAFT)->delete();

        return true;
    }


    /**
     * Function to generate Assessment ID
     * @param $patientId
     * @return string
     */
    public function generateAssessmentID($patientId)
    {   
        $patientId = 1000 + $patientId;
        return CareplanAssessment::ASSESSMENT_PREFIX . $patientId . today()->format('mdy');
    }


    /**
     * To get assessment list by patient and careplan id
     * @param $patientId
     * @param int $careplanId
     * @return mixed
     */
    public function getLists($patientId, $careplanId = 0)
    {
        $list = CareplanAssessment::where(['status' => CareplanAssessment::ACTIVE, 'patient_id' => $patientId]);

        if ($careplanId) {
            $list = $list->where(['careplan_id' => $careplanId]);
        }
        $list = $list->with('user', 'careplan', 'userRole', 'intervention')->latest()->paginate(CASELOAD_PAGINATION_COUNT);
        return $list;
    }
}