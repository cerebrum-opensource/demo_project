<?php

namespace App\Http\Controllers\Careplan;

use App\Http\Controllers\ApiController;
use App\Models\ManageableField;
use Illuminate\Http\Request;
use App\Services\{GoalReview as GoalReviewService, Careplan as CareplanService};
use App\Http\Requests\Assessment\AssessmentDiagnosisRequest;
use App\Http\Requests\Assessment\GoalReview as GoalReviewRequest;


class GoalReviewController extends ApiController
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if(auth()->user()->status != 1)
            {
                auth()->logout();
                return redirect('/login');
            }
            return $next($request);
        });

        $this->goalreview = new GoalReviewService();
        $this->careplan = new CareplanService();
    }

    /**
     * Get Diagnosis List
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function getDiagnosisList(Request $request)
    {
        $assessmentId = encrypt_decrypt('decrypt', $request->get('assessment_id'));
        $diagnosisList = $this->goalreview->getDiagnosisList($assessmentId);
        $html = view('patients.caseload.assessment.goal_review.diagnosis_list', compact('diagnosisList'))->render();

        return $this->respond([
            'html' => $html
        ]);
    }

    public function deleteDiagnosis(Request $request)
    {
        $this->goalreview->deleteDiagnosis($request);
        return $this->respond([
            'status' => 'success',
            'message' => trans('message.diagnosis_deleted_successfully')
        ]);
    }


    /**
     * Get Diagnosis Goal for a patient .
     * Parameter : Request
     * @param Request $request
     * @return \Illuminate\Http\View
     * @throws \Throwable
     */
    public function getDiagnosisGoal(Request $request)
    {
        $html = '';
        $careplanGoals = $this->careplan->getCareplanDiagnosisGoalList($request);
        if($careplanGoals) {
            $html = view('patients.caseload.assessment.goal_review.goal_list', compact('careplanGoals'))->render();
        }
        return $this->respond(['html' => $html]);
    }


    /**
     * Add goal review diagnosis and goals
     * @param AssessmentDiagnosisRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function postDiagnosisGoal(AssessmentDiagnosisRequest $request)
    {
        $this->goalreview->addOrUpdate($request);

        $assessmentId = encrypt_decrypt('decrypt', $request->get('assessment_id'));

        $assessmentGoalReview = $this->goalreview->getGoalReviewByAssessmentId($assessmentId);
        $flags = ManageableField::where('type','flag')->pluck('value','id');

        $assessmentGoalReviewDatas = $this->goalreview->getGoalReviewDataByAssessmentId($assessmentId);

        $dataSaved =[];
        foreach ($assessmentGoalReviewDatas as $assessmentGoalReviewData) {
             $dataSaved['diagnosis_'.$assessmentGoalReviewData->diagnosis_id.'_'.$assessmentGoalReviewData->diagnosis_version.'_'.$assessmentGoalReviewData->goal_id.'_'.$assessmentGoalReviewData->question_id]['answer'] = $assessmentGoalReviewData->answer;
             $dataSaved['diagnosis_'.$assessmentGoalReviewData->diagnosis_id.'_'.$assessmentGoalReviewData->diagnosis_version.'_'.$assessmentGoalReviewData->goal_id.'_'.$assessmentGoalReviewData->question_id]['flag'] = $assessmentGoalReviewData->flag_id;
        }

        $html =  view('patients.caseload.assessment.goal_review.goal_review_questions',compact('assessmentGoalReview','flags','dataSaved'))->render();;

        return $this->respond([
            'status' => 'success',
            'html' => $html
        ]);
    }

    /**
     *  Add the goal and its questions
     * Parameter : GoalReviewRequest
     * @param GoalReviewRequest $request
     * @return \Illuminate\Http\View
     */
    public function postGoalData(GoalReviewRequest $request)
    {
        $this->goalreview->addItemAnswers($request);
        return $this->respond([
            'status' => 'success'
        ]);
    }


    /**
     *  Remove the assessment goal data
     * Parameter : Request
     * @param Request $request
     * @return \Illuminate\Http\View
     */
    public function removeAssessmentGoalData(Request $request){
        
        $delete = $this->goalreview->deleteAssessmentDiagnosisGoal($request);
        $status = 'success';
        if($delete){
            $message = trans('message.remove_assessment_goal');
        }
        else{
            $status = 'error';
            $message = trans('message.error_remove_assessment_goal');
        }
        return $this->respond([
            'status' => $status,
            'message'=> $message
        ]);
    }
}
