<?php

namespace App\Http\Controllers\Careplan;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Assessment\{ CheckpointPurposeRequest, ContentDiscussRequest, InterventionFollowupRequest, InterventionRequest, CheckpointRequest };

use App\Traits\PatientCheckpointTrait;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Patient as PatientService;
use App\Services\Checkpoint as CheckpointService;
use App\Services\Tool as ToolService;
use App\Services\Intervention as InterventionService;
use App\Services\Careplan as CareplanService;
use App\Models\Intervention;

use Auth;

class CheckpointController extends ApiController
{
    use PatientCheckpointTrait;

    /**
     * CheckpointController constructor.
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if(Auth::user()->status != 1)
            {
                Auth::logout();
                return redirect('/login');
            }
            return $next($request);
        });

        $this->patient = new PatientService();
        $this->checkpoint = new CheckpointService();
        $this->toolservice = new ToolService();
        $this->interventionService = new InterventionService();
        $this->careplan = new CareplanService();
    }

    /**
     * Get Checkpoint data by patient id
     * @param $patientId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index($patientId)
    {
    	$active = 'case_manager';
    	$patient = $this->patient->patientDetail($patientId, false);
        $this->checkpoint->deletePartialCheckpoints();
        try{           
            $patient_id = encrypt_decrypt('decrypt', $patientId);
        } catch (DecryptException $e) {
            abort(404);
            exit;
        }
        $activeCarePlan = $this->careplan->checkActiveCarePlan($patient_id);

    	return view('patients.caseload.checkpoint.index',compact('active','patient','activeCarePlan'));
    }

    /**
     * Add checkpoint by changing its status from draft to active
     * @param CheckpointRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addCheckpoint(CheckpointRequest $request)
    {
        $checkpoint = $this->checkpoint->updateCheckpointStatus($request);
        $request->session()->flash('message.checkpoint-level','success');
        $request->session()->flash('message.content',trans('message.patient_checkpoint_added'));
        return $this->respond([
            'checkpoint_id' => encrypt_decrypt('encrypt', $checkpoint->id),
        ]);
    }

    /**
     * Add or Update purpose for checkpoint with type id 1
     * @param CheckpointPurposeRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addOrUpdatePurpose(CheckpointPurposeRequest $request)
    {
        if($request->get('checkpoint_id')) {
            $checkpoint = $this->checkpoint->update($request);
        } else{
            $checkpoint = $this->checkpoint->add($request);
        }

        if($checkpoint) {
            return $this->respond([
                'checkpoint_id' => encrypt_decrypt('encrypt', $checkpoint->id),
                'careplan_id' => encrypt_decrypt('encrypt', $checkpoint->careplan_id)
            ]);
        }

        return $this->respondNotFound('something went wrong!');
    }

    /**
     * Render view for listing of the Tool.
     * Parameter : Request
     * @param Request $request
     * @return \Illuminate\Http\View
     * @throws \Throwable
     */
    public function toolList(Request $request)
    {    
        $tools = $this->toolservice->getToolsList($request);    
        $html = view('patients.caseload.checkpoint.tool_list',compact('tools'))->render();       
        return response()->json(['message' => trans('message.listing_found'), 'html' => $html], 200);  
    }


    /**
    * Add/Update Content Discussed for Checkpoints.
    * Parameter : Request 
    * @return \Illuminate\Http\View
    */
    public function addOrUpdateContentDiscussed(ContentDiscussRequest $request)
    {
        $checkpoint = $this->checkpoint->addOrUpdateContentDiscussed($request);
        if($checkpoint) {
            return $this->respond([
                'checkpoint_id' => encrypt_decrypt('encrypt', $checkpoint->id),
                'careplan_id' => encrypt_decrypt('encrypt', $checkpoint->careplan_id)
            ]);
        }
        return $this->respondNotFound('something went wrong!');

    }

    /**
     * Add Intervention
     * @param InterventionRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addIntervention(InterventionRequest $request)
    {
        if($request->get('intervention_id')) {
            $intervention = $this->interventionService->update($request,Intervention::TYPE_CHECKPOINT);
            $message = trans('message.intervention_updated_successfully');
        } else{
            $intervention = $this->interventionService->add($request,Intervention::TYPE_CHECKPOINT);
            $message = trans('message.intervention_saved_successfully');
        }

        if($intervention) {
            return $this->respond([
                'intervention_id' => encrypt_decrypt('encrypt', $intervention->id),
                'message' => $message,
                'status'  => 'success'
            ]);
        }
        return $this->respondNotFound('something went wrong!');
    }

    /**
     * Add Intervention followups
     * @param InterventionFollowupRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function addInterventionFollowUp(InterventionFollowupRequest $request)
    {
        $interventionFollowup = $this->interventionService->addFollowUp($request,Intervention::TYPE_CHECKPOINT);

        if($interventionFollowup) {
            $interventionFollowUps = $this->interventionService->getFollowUpList($interventionFollowup->assessment_id,Intervention::TYPE_CHECKPOINT);
            $html = view('patients.caseload.checkpoint.intervention.follow_up_list',compact('interventionFollowUps'))->render();       
           // return response()->json(['message' => trans('message.listing_found'), 'html' => $html,'status'  => 'success'], 200); 

             return $this->respond([
                'html' => $html,
                'message' => trans('message.intervention_followup_saved_successfully'),
                'status'  => 'success'
            ]);
        }

        return $this->respondNotFound('something went wrong!');

    }

    /**
     * Render view for listing of the Tool.
     * Parameter : Request
     * @param Request $request
     * @return \Illuminate\Http\View
     * @throws \Throwable
     */
    public function getFollowupList(Request $request)
    {
        $interventionFollowUps = [];
        if($request->has('id') && $request->get('id')) {
            $id =  encrypt_decrypt('decrypt', $request->get('id'));
            $interventionFollowUps = $this->interventionService->getFollowUpList($id,Intervention::TYPE_CHECKPOINT);
        }
        $html = view('patients.caseload.checkpoint.intervention.follow_up_list',compact('interventionFollowUps'))->render();
        return response()->json(['message' => trans('message.listing_found'), 'html' => $html], 200);
    }

    /**
     * View checkpoint by patient and checkpoint id
     * @param $patientId
     * @param $checkpointId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function view($patientId, $checkpointId)
    {
        $active = 'case_manager';
        $patient = $this->patient->patientDetail($patientId, false);
        $checkpointDetail = $this->checkpoint->getById($checkpointId);
        return view('patients.caseload.checkpoint.view',compact('active','patient','checkpointDetail'));
    }

    /**
     * @param $patientId
     * @param $interventionId
     * @return string
     * @throws \Throwable
     */
    public function viewIntervention($patientId, $interventionId)
    {
        $active = 'case_manager';
        $patient = $this->patient->patientDetail($patientId, false);
        $detail = $this->interventionService->getById($interventionId);
        $type = 1;
        return view('patients.caseload.checkpoint.view.intervention.view')->with('intervention',$detail['intervention'])->with('interventionFollowUps',$detail['interventionFollowUps'])->with('careTeam',$detail['careTeam'])->with('type',$type)->with('active',$active)->with('patient',$patient)->render();
    }

    public function updateFollowupCompleteStatus(Request $request)
    {
        if ($request->has('id') && $request->get('id')) {
            $followupId = encrypt_decrypt('decrypt', $request->get('id'));
            $this->interventionService->updateFollowupCompleteStatus($followupId);
            return $this->respond([
                'status' => 'success',
                'message' => ''
            ]);
        }
    }

    /**
     * Get Tabs
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTabs(Request $request)
    {
        $default = 'handlePurposeTab';
        if($request->has('checkpoint_id') && $request->get('checkpoint_id')) {
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
