<?php

namespace App\Http\Controllers\Careplan;

use App\Http\Controllers\ApiController;
use App\Http\Requests\{Assessment\AssessmentCreateRequest,
    Assessment\AssessmentPurposeRequest,
    Assessment\ContentDiscussRequest,
    Assessment\InterventionFollowupRequest,
    Assessment\InterventionRequest,
    AssessmentAddBarrierRequest,
    AssessmentAddProgressNotesRequest,
    AssessmentAddVitalRequest,
    Assessment\RiskAssessment};
use App\Models\Intervention;
use App\Models\InterventionFollowup;
use App\Traits\PatientAssessmentTrait;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\{Patient as PatientService,
    Assessment as AssessmentService,
    Intervention as InterventionService,
    Checkpoint as CheckpointService,
    GoalReview as GoalReviewService,
    Careplan as CareplanService};
use Auth;



class AssessmentController extends ApiController
{
    use PatientAssessmentTrait;

	public function __construct(){
        $this->middleware(function ($request, $next) {
            if(Auth::user()->status != 1)
            {
                Auth::logout();
                return redirect('/login');
            }
            return $next($request);
        });

        $this->patient = new PatientService();
        $this->assessment = new AssessmentService();
        $this->intervention = new InterventionService();
        $this->careplan = new CareplanService();
        $this->goalreview = new GoalReviewService();
    }

    /**
     * Load assessment view to create assessment
     * @param $patientId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index($patientId)
    {
    	$active = 'case_manager';
    	$patient = $this->patient->patientDetail($patientId, false);
        $this->assessment->deletePartialAssessments();
        try{           
            $patient_id = encrypt_decrypt('decrypt', $patientId);
        } catch (DecryptException $e) {
            abort(404);
            exit;
        }
        $activeCarePlan = $this->careplan->checkActiveCarePlan($patient_id);
    	return view('patients.caseload.assessment.index',compact('active','patient','activeCarePlan'));
    }

    /**
     * View Assessment
     * @param $patientId
     * @param $assignmentId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function view($patientId, $assignmentId)
    {
        $active = 'case_manager';
        $patient = $this->patient->patientDetail($patientId, false);
        $assignmentId = encrypt_decrypt('decrypt', $assignmentId);
        $assessment = $this->assessment->getById($assignmentId);
        return view('patients.caseload.assessment.view',compact('active','patient','assessment'));
    }

    /**
     * Clone assessment
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function copyAssessment(Request $request)
    {
        $assessmentId = encrypt_decrypt('decrypt', $request->get('assessment_id'));
        $assessment = $this->assessment->copy($assessmentId);

        $request->session()->flash('message.assessment-level','success');
        $request->session()->flash('message.content',trans('message.patient_assessment_copied'));
        return $this->respond([
            'redirectUrl' => route('edit_copy_assessment', [
                'patient_id' => encrypt_decrypt('encrypt',$assessment->patient_id),
                'assessment_id' => encrypt_decrypt('encrypt',$assessment->id),
            ])
        ]);
    }

    /**
     * Edit Copied Assessment
     * @param $patientId
     * @param $assessmentId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editCopiedAssessment($patientId, $assessmentId)
    {
        $active = 'case_manager';
        $assessmentId = encrypt_decrypt('decrypt', $assessmentId);
        $patient = $this->patient->patientDetail($patientId, false);
        $assessment = $this->assessment->getById($assessmentId);

        return view('patients.caseload.assessment.copy',compact('active','patient','assessment'));
    }

    /**
     * Add or Update purpose
     * @param AssessmentPurposeRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addOrUpdatePurpose(AssessmentPurposeRequest $request)
    {
        if($request->get('assessment_id')) {
            $assessmentId = encrypt_decrypt('decrypt', $request->get('assessment_id'));
            $formData = $request->except('_token','tab_name','patient_id','careplan_id','assessment_id');
            $assessment = $this->assessment->updateAssessment($formData, $assessmentId);
         //   $assessment = $this->assessment->updatePurpose($request);
        } else{
            $assessment = $this->assessment->addPurpose($request);
        }

        if($assessment) {
            return $this->respond([
                'assessment_id' => encrypt_decrypt('encrypt', $assessment->id),
                'careplan_id' => encrypt_decrypt('encrypt', $assessment->careplan_id)
            ]);
        }

        return $this->respondNotFound('something went wrong!');
    }

    /**
     * Save Or Update Risk Assessment
     * @param RiskAssessment $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveQuestion(RiskAssessment $request)
    {
       $response = $this->assessment->addItemsAnswers($request);
        if ($response) {
            return $this->respond(['message' => 'answers added successfully.']);
        }

        return $this->setStatusCode(500)->respond('Something went wrong!.');
    }

    /**
     * Search Barriers
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchBarriers(Request $request)
    {
        $barriers = $this->assessment->searchBarrier($request);
        return $this->respond(['data' => $barriers]);
    }

    /**
     * Add Barrier
     * @param AssessmentAddBarrierRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function addBarrier(AssessmentAddBarrierRequest $request)
    {
        $formData = [];
        $formData['assessment_id'] = encrypt_decrypt('decrypt', $request->get('assessment_id'));
        $formData['patient_id'] = encrypt_decrypt('decrypt', $request->get('patient_id'));
        $formData['careplan_id'] = encrypt_decrypt('decrypt', $request->get('careplan_id'));
        $formData['barrier_id'] = $request->get('barrier_id');

        $response = $this->assessment->addBarrier($formData);

        if ($response) {
            $assessmentBarriers = $this->assessment->getBarrierList($formData['assessment_id']);
            $is_careplan = false;
            $html = view('patients.caseload.assessment.barriers_list', compact('assessmentBarriers','is_careplan'))->render();
            return $this->respond([
                'message' => trans('message.assessment_barrier_added_successfully'),
                'status' => 'success',
                'html' => $html
            ]);
        }
    }

    /**
     * Get Barriers list
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function getBarrierList(Request $request)
    {
        $assessmentId = encrypt_decrypt('decrypt', $request->get('assessment_id'));
        $assessmentBarriers = $this->assessment->getBarrierList($assessmentId);
        $is_careplan = false;
        $html = view('patients.caseload.assessment.barriers_list', compact('assessmentBarriers','is_careplan'))->render();

        return $this->respond([
            'html' => $html
        ]);
    }

    /**
     * Get Barrier By Id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function getBarrierById(Request $request)
    {
        $barrierId = encrypt_decrypt('decrypt', $request->get('id'));
        $barrier = $this->assessment->getBarrierById($barrierId);
        $html = view('patients.caseload.assessment.barriers_detail', compact('barrier'))->render();

        return $this->respond([
            'html' => $html
        ]);
    }

    /**
     * Delete Barrier
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteBarrier(Request $request)
    {
        $assessmentBarrierId = encrypt_decrypt('decrypt', $request->get('id'));
        $response = $this->assessment->deleteBarrier($assessmentBarrierId);

        if($response) {
            return $this->respond([
                'message' => trans('message.assessment_barrier_deleted_successfully'),
                'status' => 'success'
            ]);
        }

        return $this->setStatusCode(500)->respond('Something went wrong.');
    }

    /**
     * Add Assessment Vital
     * @param AssessmentAddVitalRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function addVital(AssessmentAddVitalRequest $request)
    {
        $assessmentId = encrypt_decrypt('decrypt', $request->get('assessment_id'));
        $this->assessment->addVital($request, $assessmentId);

        $vitals = $this->assessment->getVitalList($assessmentId);
        $html = view('patients.caseload.assessment.vital_list', compact('vitals'))->render();

        return $this->respond([
            'message' => trans('message.assessment_vital_added_successfully'),
            'status' => 'success',
            'html' => $html,
        ]);
    }

    /**
     * Get Vital List by assessmentId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function getVitalsList(Request $request)
    {
        $assessmentId = encrypt_decrypt('decrypt', $request->get('assessment_id'));
        $vitals = $this->assessment->getVitalList($assessmentId);
        $html = view('patients.caseload.assessment.vital_list', compact('vitals'))->render();

        return $this->respond([
            'html' => $html
        ]);
    }

    /**
     * Save Overall Progress Notes for assessment
     * @param AssessmentAddProgressNotesRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveProgressNote(AssessmentAddProgressNotesRequest $request)
    {
        $assessmentId = encrypt_decrypt('decrypt', $request->get('assessment_id'));

        if($request->progress_notes){
            $request->request->add(['overall_notes'=>$request->progress_notes]);
        }
        $formData = $request->except('_token','tab_name','patient_id','careplan_id','assessment_id','progress_notes');
        $response = $this->assessment->updateAssessment($formData, $assessmentId);
        if ($response) {
            return $this->respond([
                'message' => trans('message.assessment_progress_notes_saved_successfully'),
                'status'  => 'success'
            ]);
        }
    }

    /**
     * Update Content Discussed
     * @param ContentDiscussRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateContentDiscussed(ContentDiscussRequest $request)
    {
        $formData = $request->except('_token','tab_name','patient_id','careplan_id','assessment_id');
        $assessmentId = encrypt_decrypt('decrypt', $request->get('assessment_id'));
        $response = $this->assessment->updateAssessment($formData, $assessmentId);
        if ($response) {
            return $this->respond([
                'message' => trans('message.assessment_content_discussed_saved_successfully'),
                'status'  => 'success'
            ]);
        }
    }

    /**
     * Add Or Update Intervention
     * @param InterventionRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addIntervention(InterventionRequest $request)
    {

        if($request->get('intervention_id')) {
            $intervention = $this->intervention->update($request,Intervention::TYPE_ASSESSMENT);
        } else{
            $intervention = $this->intervention->add($request,Intervention::TYPE_ASSESSMENT);
        }

        if($intervention) {
            return $this->respond([
                'intervention_id' => encrypt_decrypt('encrypt', $intervention->id)
            ]);
        }
        return $this->respondNotFound('something went wrong!');
    }

    /**
     * Add Intervention Followp
     * @param InterventionFollowupRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function addInterventionFollowUp(InterventionFollowupRequest $request)
    {
        $interventionFollowup = $this->intervention->addFollowUp($request,Intervention::TYPE_ASSESSMENT);

        if($interventionFollowup) {
            $interventionFollowUps = $this->intervention->getFollowUpList($interventionFollowup->assessment_id,InterventionFollowup::TYPE_ASSESSMENT);
            $html = view('patients.caseload.assessment.intervention.follow_up_list',compact('interventionFollowUps'))->render();
            return response()->json(['message' => trans('message.listing_found'), 'html' => $html], 200);
        }

        return $this->respondNotFound('something went wrong!');
    }

    /**
     * Get Intervention Followup list
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function getFollowupList(Request $request)
    {
        $assessmentId = encrypt_decrypt('decrypt', $request->get('assessment_id'));
        $interventionFollowUps = $this->intervention->getFollowUpList($assessmentId,InterventionFollowup::TYPE_ASSESSMENT);
        $html = view('patients.caseload.assessment.intervention.follow_up_list',compact('interventionFollowUps'))->render();
        return response()->json(['message' => trans('message.listing_found'), 'html' => $html], 200);
    }

    /**
     * Add Assessment by changing status from draft to active
     * @param AssessmentCreateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addAssessment(AssessmentCreateRequest $request)
    {
        $response = $this->assessment->add($request);
        $request->session()->flash('message.assessment-level','success');
        $request->session()->flash('message.content',trans('message.patient_assessment_added'));
        if($response) {
            return $this->respond([
                'status' => 'success',
                'message' => 'Assessment created successfully',
            ]);
        }

        return $this->respondNotFound('No assessment found');
    }


    /**
     * Get Tabs
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTabs(Request $request)
    {
        $default = 'handlePurposeTab';
        if($request->has('assessment_id') && $request->get('assessment_id')) {
            $tab = $request->input('tab');
            $method =  'handle'.ucfirst(camel_case($tab)).'Tab';

            if(method_exists($this,$method)) {
                return $this->respond([
                    'data' => $this->$method()
                ]);
            }
        } else {
            return $this->setStatusCode(403)->respond([
                'message' => 'Invalid Access',
            ]);
        }

        return $this->respondNotFound('Method not found');
    }

}
