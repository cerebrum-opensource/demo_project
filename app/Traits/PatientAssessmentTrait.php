<?php
/**
 * Created by PhpStorm.
 * User: gopal
 * Date: 5/4/19
 * Time: 11:06 AM
 */

namespace App\Traits;

use App\Models\Admin\CarePlan\ContentDiscussed;
use App\Models\CareplanAssessment;
use App\Models\CareplanAssessmentGoalReview;
use App\Models\CareplanItemHistory;
use App\Models\Intervention;
use App\Models\InterventionFollowup;
use App\Models\User;
use App\Models\CareplanAssessmentTeam;
use Illuminate\Support\Facades\DB;
use App\Models\ManageableField;

trait PatientAssessmentTrait {

    public function handleVitalsTab()
    {
        $tabContent = [];
        $assessmentId = encrypt_decrypt('decrypt', request()->get('assessment_id'));
        $vitals = $this->assessment->getVitalList($assessmentId);
        $tabContent['html'] = view('patients.caseload.assessment.vitals', compact('vitals'))->render();
        return $tabContent;
    }

    public function handleProgressNotesTab()
    {
        $tabContent = [];
        $assessmentId = encrypt_decrypt('decrypt', request()->get('assessment_id'));
        $assessment = $this->assessment->getById($assessmentId);
        $tabContent['html'] = view('patients.caseload.assessment.progress_notes', compact('assessment'))->render();
        return $tabContent;
    }


    public function handleBarriersTab()
    {
        $tabContent = [];
        $assessmentId = encrypt_decrypt('decrypt', request()->get('assessment_id'));
        $assessmentBarriers = $this->assessment->getBarrierList($assessmentId);
        $tabContent['html'] = view('patients.caseload.assessment.barriers', compact('assessmentBarriers'))->render();
        return $tabContent;
    }

    public function handleInterventionTab()
    {
        $tabContent = [];
        $interventionFollowUps = [];
        $intervention = new Intervention();
        $type = 0;

        $flags = ManageableField::where('type','flag')->pluck('value','id');

        if(request()->has('assessment_id') && request()->get('assessment_id')){
            $assessmentId = encrypt_decrypt('decrypt', request()->get('assessment_id'));
            $intervention = Intervention::where('type', Intervention::TYPE_ASSESSMENT)->where('type_id',$assessmentId)->first();
            $interventionFollowUps = $this->intervention->getActiveFollowUpList($assessmentId,InterventionFollowup::TYPE_ASSESSMENT);
        }

        $careplanId = encrypt_decrypt('decrypt', request()->get('careplan_id'));
        $careTeam = CareplanAssessmentTeam::where('careplan_id', $careplanId)->first();
         $tabContent['html'] = view('patients.caseload.assessment.intervention.index', compact('intervention','careTeam','interventionFollowUps','type','flags'))->render();
        if(request()->has('is_view') && request()->get('is_view')) {
            $careTeam = User::where('id', $intervention->assigned_users)->pluck('name','id');
             $tabContent['html'] = view('patients.caseload.assessment.intervention.view', compact('intervention','careTeam','interventionFollowUps','type','flags'))->render();
        }
        
       
        return $tabContent;
    }

    public function handleGoalReviewTab()
    {
        $tabContent = [];
        $assessmentId = encrypt_decrypt('decrypt', request()->get('assessment_id'));
        $assessmentGoalReview = $this->goalreview->getGoalReviewByAssessmentId($assessmentId);
        $assessmentGoalReviewDatas = $this->goalreview->getGoalReviewDataByAssessmentId($assessmentId);
        
        $dataSaved =[];
        foreach ($assessmentGoalReviewDatas as $assessmentGoalReviewData) {
            # code...
          //  print_r($assessmentGoalReviewData);
            $dataSaved['diagnosis_'.$assessmentGoalReviewData->diagnosis_id.'_'.$assessmentGoalReviewData->diagnosis_version.'_'.$assessmentGoalReviewData->goal_id.'_'.$assessmentGoalReviewData->question_id]['answer'] = $assessmentGoalReviewData->answer;
             $dataSaved['diagnosis_'.$assessmentGoalReviewData->diagnosis_id.'_'.$assessmentGoalReviewData->diagnosis_version.'_'.$assessmentGoalReviewData->goal_id.'_'.$assessmentGoalReviewData->question_id]['flag'] = $assessmentGoalReviewData->flag_id;

          //  die();
        }
        
        //print_r($dataSaved);
        $flags = ManageableField::where('type','flag')->pluck('value','id');

        $tabContent['html'] =  view('patients.caseload.assessment.goal_review.index',compact('assessmentGoalReview','flags','dataSaved'))->render();;
        return $tabContent;
    }

    public function handleContentDiscussedTab()
    {
        $tabContent = [];
        $contentDiscussed = [];
        $assessmentId = encrypt_decrypt('decrypt', request()->get('assessment_id'));
        $assessment = CareplanAssessment::findOrFail($assessmentId);
        if($assessment->visit_content) {
            $contentDiscussed = ContentDiscussed::select(
                DB::raw("CONCAT(code,' | ',title) AS code_name"),'id')
                ->whereIn('id', $assessment->visit_content)->pluck('code_name','id');
        }

        $tabContent['html'] = view('patients.caseload.assessment.content_discussed', compact('contentDiscussed','assessment'))->render();

        if(request()->has('is_view') && request()->get('is_view')) {
            $tabContent['html'] = view('patients.caseload.assessment.view.content_discussed', compact('contentDiscussed','assessment'))->render();
        }
        return $tabContent;
    }

    public function handlePriorityAlignmentTab()
    {
        $tabContent = [];
        $patientId = encrypt_decrypt('decrypt', request()->get('patient_id'));
        $assessmentId = encrypt_decrypt('decrypt', request()->get('assessment_id'));

        $data = $this->assessment->getPriorityAlignment($patientId, $assessmentId);
        $tabContent['html'] = view('patients.caseload.assessment.priority_alignment')->with('data', $data)->render();
        return $tabContent;
    }

    public function handleRiskAssessmentTab()
    {
        $tabContent = [];
        $patientId = encrypt_decrypt('decrypt', request()->get('patient_id'));
        $assessmentId = encrypt_decrypt('decrypt', request()->get('assessment_id'));

        $data = $this->assessment->getRiskAssessment($patientId, $assessmentId);
        $tabContent['html'] = view('patients.caseload.assessment.risk_assessment')->with('data', $data)->render();
        return $tabContent;
    }

    public function handlePurposeTab()
    {
        $assessment = new CareplanAssessment();
        if(request()->has('assessment_id') && request()->get('assessment_id')) {
            $assessmentId = encrypt_decrypt('decrypt', request()->get('assessment_id'));
            $assessment = CareplanAssessment::findOrFail($assessmentId);
        }

        $tabContent = [];
        $tabContent['html'] = view('patients.caseload.assessment.purpose')->with('assessment',$assessment)->render();
        return $tabContent;
    }

}