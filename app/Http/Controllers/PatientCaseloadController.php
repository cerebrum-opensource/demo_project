<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ApiController;
use App\Models\{ CareplanGoal, PatientCareplan, State, User, Patient, ManageableField, PatientForm, PatientSignature };
use App\Models\Admin\CarePlan\GoalVersion;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Encryption\DecryptException;
use App\Services\{Careplan as CareplanService,
    Patient as PatientService,
    Allergy as AllergyService,
    Medication as MedicationService,
    Diagnosis as DiagnosisService,
    Checkpoint as CheckpointService,
    Intervention as InterventionService,
    Assessment as AssessmentService};
use App\Http\Requests\{AddCareplanDiagnosisRequest,
    AllergyStatusRequest,
    MedicationStatusRequest,
    CareplanStatusRequest,
    PatientMedication,
    PatientReleaseForm,
    PatientUpdate as PatientUpdateRequest,
    PatientMedication as PatientMedicationRequest,
    PatientAllergy as PatientAllergyRequest,
    Careplan as CareplanRequest};
use Session;
use Auth;
use Validator;
use View;
use Helper;
use Carbon\Carbon;
  

class PatientCaseloadController extends ApiController
{
    
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
        $this->allergy = new AllergyService();
        $this->medication = new MedicationService();
        $this->diagnosisService = new DiagnosisService();
        $this->careplan = new CareplanService();
        $this->checkpoint = new CheckpointService();
        $this->intervention = new InterventionService();
        $this->assessment = new AssessmentService();
    }


    /**
    * Render view for listing of the Case Loads.
    * Parameter : Request 
    * @return \Illuminate\Http\View
    */

    public function getIndex(Request $request)
    {    
        $active = 'case_load';
       
        if ($request->ajax()) {
            $data = $this->patient->patientLists($request,true);
            $patients =  $data['patients']->caseload()->paginate(PAGINATION_COUNT_10);
            $request->flashOnly(['status','from_date','to_date','selected_cm','selected_md','selected_chw']);
            return view('users.table', ['patients' => $patients])->with('active', $active)->render();  
        }
        else {
            $data = $this->patient->patientLists($request,false);
            $data['patients'] = $data['patients']->caseload()->paginate(PAGINATION_COUNT_10);
            $request->flashOnly(['status','from_date','to_date','selected_cm','selected_md','selected_chw']);
            return view('users.users_list',$data)->with('active', $active);
        }        
    }


    /**
    * Render view for Patient detail .
    * Parameter : $patientId 
    * @return \Illuminate\Http\View
    */

    public function getPatientView($patientId)
    {
        $active = 'case_manager';
        $patient = $this->patient->patientDetail($patientId, true);
        session()->put('previous_patient_id', $patientId);
        $careTeam = User::getPatientCareTeamList();
        try{           
            $patient_id = encrypt_decrypt('decrypt', $patientId);;
        } catch (DecryptException $e) {
            abort(404);
            exit;
        }

        $previous_medications = $this->medication->medicationList($patient_id);
        $previous_allergies = $this->allergy->allergyLists($patient_id);
        $patientConsentForm = $this->patient->getPatientConsentForm($patient_id);
        $patientHippaForm = $this->patient->getPatientHippaForm($patient_id);
        $carePlanList = $this->careplan->getCareplanList($patient_id);
        $checkpointList = $this->checkpoint->getLists($patient_id);
        $assessmentList = $this->assessment->getLists($patient_id);
        $interventionList = $this->intervention->getLists($patient_id);
        $activeCarePlan = $this->careplan->checkActiveCarePlan($patient_id);
        return view('patients.caseload.view_patient',compact('active', 'patient', 'careTeam','previous_allergies','previous_medications','patientConsentForm','patientHippaForm','carePlanList','checkpointList','interventionList','assessmentList','activeCarePlan'));
    }


    /**
    * update the Patient detail and return the updated view
    * Parameter : PatientUpdateRequest 
    * @return \Illuminate\Http\View
    */

    public function updatePatientInfo(PatientUpdateRequest $request){
        $response = $this->patient->updatePatientDetail($request);
        if($response)
            {
                $patient = $this->patient->patientDetail($request->patient_id, false);
                $profile_html = View::make('patients.caseload.sections.patient_profile_details')->with('patient', $patient['patient_info'])->render();
                $modal_html = View::make('patients.caseload.edit_patient_info_modal')->with('patient', $patient['patient_info'])->render();
                $patient_header = View::make('patients.caseload.sections.header-patient-info')->with('patient', $patient['patient_info'])->with('is_form', false)->render();
           //     $request->session()->flash('message.level','success');
            //    $request->session()->flash('message.content',trans('message.patient_detail_successfully'));
                
                return response()->json(['message'=> trans('message.patient_detail_successfully'),'status'=>1,'profile_html' =>$profile_html,'modal_html' =>$modal_html,'patient_header'=>$patient_header],200);
            }
            else
            {   
                $request->session()->flash('message.level','danger');
                $request->session()->flash('message.content',trans('message.error_updated_patient'));
                return response()->json(['message'=>trans('message.error_updated_patient'),'status'=>0],202);            
            }

    }


    /**
     * update the Patient Care Pan and return the updated view
     * Parameter : PatientUpdateRequest
     * @return \Illuminate\Http\View
     * @throws \Throwable
     */

    public function updateCareplanTeam(PatientUpdateRequest $request)
    {    
        $response = $this->patient->editCareplanTeam($request);
        if($response)
            {  
                $patient = $this->patient->patientDetail($request->patient_id, false);
                $html = view('patients.caseload.sections.careplan_team')->with('patient', $patient['patient_info'])->render();
                return response()->json(['message'=> trans('message.care_team_updated'),'status'=>1,'html' =>$html],200);
            }
            else
            {
                return response()->json(['message'=>trans('message.error_care_team'),'status'=>0],202);            
            }
    }


    /**
     * get the diagnosis detail with list of goal
     * Parameter : Request
     * @return \Illuminate\Http\View
     * @throws \Throwable
     */

    public function getDiagnosisDetail(Request $request)
    {
        $diagnosisHtml = '';
        $diagnosisId = $request->get('diagnosis_id');
        $careplanId = 0;
        $goalIds = '';

        if($request->has('careplan_id')) {
            $careplanId = encrypt_decrypt('decrypt',$request->get('careplan_id'));
        }


        if(!$request->has('page')) {
            $diagnosis = $this->careplan->getDiagnosisDetail($diagnosisId, $careplanId);

            $diagnosisHtml = view('patients.caseload.care_plan.diagnosis_detail', compact('diagnosis'))->render();
        }

        $goals = $this->careplan->getDiagnosisGoals($diagnosisId, $careplanId);
        $goalsHtml = view('patients.caseload.care_plan.diagnosis_goal_list', compact('goals','diagnosisId','careplanId'))->render();

        if($request->has('goal_ids')) {
            $goalIds = $this->careplan->getDiagnosisSelectedGoalIds($diagnosisId, $careplanId);
        }

        return $this->respond([
            'diagnosis_detail' => $diagnosisHtml,
            'goal_list' => $goalsHtml,
            'goal_ids' => $goalIds
        ]);
    }


    /**
    * get the Assessments notes list for CHW/MD/CM
    * Parameter : Request ,$patient_id,$user_type
    * @return \Illuminate\Http\View
    */

    public function getAssessments($patient_id,$user_type,Request $request)
    {
        $active = 'case_manager';
        $patient = $this->patient->patientDetail($patient_id, false);
        $assessments = $this->patient->getAssessmentsNotesList($patient_id,$user_type,$request);
        return view('patients.caseload.sections.view-assessment',compact('active', 'patient', 'assessments'));
    }


    /**
    * Save the medication for patient
    * Parameter : PatientMedicationRequest
    * @return \Illuminate\Http\View
    */

    public function postMedicationSave(PatientMedicationRequest $request)
    {
        $response = $this->medication->addMedication($request);

        if ($response) {
            $previous_medications = $this->medication->medicationList($request->patient_id);
            $type = 'case_load';
            $is_careplan = 0;
            $html = view('patients.medications.medication_listing',compact('active', 'previous_medications', 'patient_id','type','is_careplan'))->render();

            $message = trans('message.medication_created_successfully');
            if($request->action != 'add') {
                $message = trans('message.medication_updated_successfully');
            }

            $request->session()->flash('message.level','success');
            $request->session()->flash('message.content',$message);
            return $this->respond([
                'message'=> $message,
                'html' => $html
            ]);
        }

        $request->session()->flash('message.level','danger');
        $request->session()->flash('message.content',trans('message.error_medication_created'));
        return response()->json(['message'=>trans('message.error_medication_created')],200);
    }


    /**
    * Save the Allergy for patient
    * Parameter : PatientAllergyRequest
    * @return \Illuminate\Http\View
    */


    public function postAllergySave(PatientAllergyRequest $request)
    {
        $response = $this->allergy->addAllergy($request); 
        if ($response) {
            $previous_allergies = $this->allergy->allergyLists($request->patient_id);
            $type = 'case_load';
            $is_careplan = 0;
            $html = view('patients.allergies.allergies_listing',compact('active', 'previous_allergies', 'patient_id','type','is_careplan'))->render();


            $message = trans('message.allergy_created_successfully');
            if($request->action != 'add') {
                $message = trans('message.allergy_updated_successfully');
            }

            $request->session()->flash('message.level','success');
            $request->session()->flash('message.content',$message);
            return $this->respond([
                'message'=> $message,
                'html' => $html
            ]);
        }
      
        $request->session()->flash('message.level','danger');
        $request->session()->flash('message.content',trans('message.error_medication_created'));
        return response()->json(['message'=>trans('message.error_medication_created')],200);
    }

   /**
    * update medication for patient
    * Parameter : MedicationStatusRequest
    * @return \Illuminate\Http\View
    */

    public function updateMedicationStatus(MedicationStatusRequest $request)
    {
        $medication = $this->medication->changeStatus($request);
        $message = trans('message.medication_started_successfully');

        if(!$medication->status) {
            $message = trans('message.medication_discontinued_successfully');
        }

        return $this->respond([
            'status' => 'success',
            'message'=> $message
        ]);
    }

    /**
    * update allergy for patient
    * Parameter : AllergyStatusRequest
    * @return \Illuminate\Http\View
    */

    public function updateAllergyStatus(AllergyStatusRequest $request)
    {
        $allergy = $this->allergy->changeStatus($request);
        $message = trans('message.allergy_started_successfully');

        if(!$allergy->status) {
            $message = trans('message.allergy_discontinued_successfully');
        }

        return $this->respond([
            'status' => 'success',
            'message'=> $message
        ]);
    }


     /**
    * list of allergy for patient
    * Parameter : patient_id
    * @return \Illuminate\Http\View
    */

    public function getAllergyList($patient_id)
    {
        try{     
            $patient_id = encrypt_decrypt('decrypt',$patient_id);
        } catch (\Exception $e) {
            abort(404);
            exit;
        }
        $active = '';
        $previous_allergies = $this->allergy->allergyLists($patient_id);
        $type = 'case_load';
        if(request()->is_careplan)
        $is_careplan = 1;
        else
        $is_careplan = 0;
        $html = view('patients.allergies.allergies_listing',compact('active', 'previous_allergies', 'patient_id','type','is_careplan'))->render();       
        return response()->json(['message' => trans('message.listing_found'), 'html' => $html], 200);
    }

     /**
    * list of medications for patient
    * Parameter : patient_id
    * @return \Illuminate\Http\View
    */

    public function getMedicationList($patient_id)
    {
        try{     
            $patient_id = encrypt_decrypt('decrypt',$patient_id);
        } catch (\Exception $e) {
            abort(404);
            exit;
        }
        $active = '';
        $previous_medications = $this->medication->medicationList($patient_id);
        $type = 'case_load';
        if(request()->is_careplan)
        $is_careplan = 1;
        else
        $is_careplan = 0;
        $html = view('patients.medications.medication_listing',compact('active', 'previous_medications', 'patient_id','type','is_careplan'))->render();
        return response()->json(['message' => trans('message.listing_found'), 'html' => $html], 200);
    }


    /**
    * Render view for history and detial of the medication.
    * Parameter : patient_id,Request
    * @return \Illuminate\Http\View
    */
    public function getMedicationDetail($patient_id,Request $request)
    {
        try{     
            $patient_id = encrypt_decrypt('decrypt',$patient_id);
            $id = encrypt_decrypt('decrypt',$request->id);
            
        } catch (\Exception $e) {
            abort(404);
            exit;
        }
        $medication = $this->medication->medicationDetail($id);

        $type = 'case_load';
        $html = view('patients.medications.medication_history',compact('active', 'medication'))->render();
        return $this->respond(['message' => '', 'html' => $html]);
    }


    /**
    * Render view for history and detial of the allergy.
    * Parameter : patient_id,Request
    * @return \Illuminate\Http\View
    */
    public function getAllergyDetail($patient_id,Request $request)
    {
        try{
            $patient_id = encrypt_decrypt('decrypt',$patient_id);
            $id = encrypt_decrypt('decrypt',$request->id);

        } catch (\Exception $e) {
            abort(404);
            exit;
        }
        $allergy = $this->allergy->allergyDetail($id);

        $type = 'case_load';
        $html = view('patients.allergies.allergy_history',compact('active', 'allergy'))->render();
        return $this->respond(['message' => '', 'html' => $html]);
    }

     
    /**
    * Render view for release form .
    * Parameter : patient_id,Request,form_type
    * @return \Illuminate\Http\View
    */ 

    public function getReleaseForm($patient_id,$form_type,Request $request){

        try{           
            $type = encrypt_decrypt('decrypt',$form_type);
        } catch (\Exception $e) {
            abort(404);
            exit;
        }
        $lang = 'eng';
        if($request->has('lang')){
            $lang = $request->input('lang');
        }
        $patient = $this->patient->patientDetail($patient_id, false);
        $patient = $patient['patient_info'];
        $patient_id = $patient->id;
        $active = 'case_load';
       // $patient_form = new PatientForm();
        
        
        
        if($type == 'consent'){
            $patient_form_data = $this->patient->getPatientConsentForm($patient_id);
            if(@$patient_form_data['consent_form_language'])
                $lang = @$patient_form_data['consent_form_language'];
            
             if(@$patient_form_data['signature'])
                $patient->consent_form_signature_setup = $patient_form_data['signature'];

            return view('patients.caseload.forms.consent_form',compact('active', 'patient','lang','patient_id','patient_form_data','type'));
        }
        else if($type == 'hippa' ){
             $patient_form_data = $this->patient->getPatientHippaForm($patient_id);
            if(@$patient_form_data['hippa_form_language'])
                $lang = @$patient_form_data['hippa_form_language'];
            
             if(@$patient_form_data['signature'])
                $patient->hippa_form_signature_setup = $patient_form_data['signature'];

            return view('patients.caseload.forms.hippa_form',compact('active', 'patient','lang','patient_id','patient_form_data','type'));
        }
        else{
            abort(404);
        }
        

    }


    /**
    * Render view for careplan .
    * Parameter : patientId
    * @return \Illuminate\Http\View
    */ 

    public function getCarePlan($patientId)
    {
        $active = 'case_manager';
        $this->patient->deletePartialRecords($patientId);

        $patient = $this->patient->patientDetail($patientId, false);
        return view('patients.caseload.care_plan.care-plan',compact('active', 'patient'));
    }


    /**
    * Render view for diagnosis for careplan after save it .
    * Parameter : Request
    * @return \Illuminate\Http\View
    */

    public function postDiagnosisList(Request $request)
    {
       $diagnosis = $this->diagnosisService->getDiagnosisListForDropDown($request);
       return response()->json(['html' => $diagnosis], 200);
    }


    /**
    * Save release form for a patient .
    * Parameter : Request
    * @return \Illuminate\Http\View
    */    

    public function postPatientReleaseForm(PatientReleaseForm $request){
       
        if($request->form_type == 'consent'){
          /*  $validator = Validator::make($request->all(),[
                'consent_form_signature_setup' => 'required',            
                'acknowledge_receive_services' => 'required|in:1',
                'acknowledge_emergency_medical_services' => 'required|in:1',
                'acknowledge_release_medical_records' => 'required|in:1',
                'acknowledge_release_vehicle' => 'required|in:1',
                'acknowledge_patient_bill_of_rights' => 'required|in:1',
                'acknowledge_signature' => 'required|in:1',
                'consent_form_documents_located_at_with' => 'required',
                'consent_form_living_will_executed' => 'required',
                'consent_form_dpoa_executed' => 'required',
                'consent_form_signature_date' => 'required',
                'consent_form_patient_initials' => 'required|max:10',
                'patient_id' => 'required',
                'consent_form_living_will_executed' => 'required',
                'consent_form_dpoa_name' => 'required|max:50|regex:/^[\pL\s]+$/u',
                'consent_form_dpoa_phone_number' => 'required|phone',
            ]);*/
            $form_type = 0;
        }
        else if($request->form_type == 'hippa' ){
           /* $validator = Validator::make($request->all(),[
                'hippa_form_signature_setup' => 'required',            
                'acknowledge_authorize_person' => 'required|in:1',
                'acknowledge_description_of_info' => 'required|in:1',
                'acknowledge_purpose_to_use' => 'required|in:1',
                'acknowledge_validity_of_form' => 'required|in:1',
                'acknowledge_signature' => 'required|in:1',
                'patient_id' => 'required'
            ]);*/
            $form_type = 1;
        }
        else{
            abort(404);
        }
        

        /*if($validator->fails()){
            return response()->json(['errors'=>$validator->errors()],422);
        }*/
        $message='';
        if($form_type == 1){
            $message='Hippa form';    
        }
        if($form_type == 0){
            
            $message='Consent form';    
        }
        $save_signature = $this->patient->savePatientReleaseForm($request,$form_type);
        if($save_signature){
            $request->session()->flash('message.level','success');
            $request->session()->flash('message.content',''. $message.trans('message.sign_successfully'));
            return response()->json(['message' => trans('message.consent_form_save')], 200);  
        }
        else{
            $request->session()->flash('message.level','danger');
            $request->session()->flash('message.content',trans('message.some_error'). $message.'.');
            return response()->json(['message' => trans('message.error_consent_form_save')], 500);
        }      
                    
    }


    /**
    * Save Care plan for a patient .
    * Parameter : CareplanRequest
    * @return \Illuminate\Http\View
    */
    public function saveCarePlan(CareplanRequest $request)
    {
        $response = $this->careplan->addCareplan($request);

        $request->session()->flash('message.care-level','success');
        $request->session()->flash('message.content',trans('message.patient_careplan_added'));
        return $this->respond(['message' => trans('message.patient_careplan_added'), 'status' => 'success']);
    }


    /**
    * Add Care plan Diagnosis for a patient .
    * Parameter : AddCareplanDiagnosisRequest
    * @return \Illuminate\Http\View
    */  


    public function addCareplanDiagnosis(AddCareplanDiagnosisRequest $request)
    {
        $patientCareplan =  $this->careplan->addCareplanDiagnosisGoal($request);
        if($patientCareplan) {

            $diagnosisList = $this->careplan->getCareplanDiagnosis($patientCareplan->id);
            $is_careplan_detail = '';
            $diagnosisHtml = view('patients.caseload.care_plan.careplan_diagnosis_list', compact('diagnosisList','is_careplan_detail'))->render();

            if($request->get('edit_diagnosis_id')) {
                return $this->respond(['message' => trans('message.update_careplan_diagnosis'), 'status' => 'success', 'careplan_id' => encrypt_decrypt('encrypt',$patientCareplan->id), 'diagnosis_html' => $diagnosisHtml]);
            } else {
                return $this->respond(['message' => trans('message.add_careplan_diagnosis'), 'status' => 'success', 'careplan_id' => encrypt_decrypt('encrypt',$patientCareplan->id), 'diagnosis_html' => $diagnosisHtml]);
            }

        }
        else{
            return $this->respond(['message' => trans('message.error_add_careplan_diagnosis'), 'status' => 'error']);
        }
    }


    /**
    * Get Care plan Diagnosis for a patient .
    * Parameter : careplanId
    * @return \Illuminate\Http\View
    */ 


    public function getCareplanDiagnosisList($careplanId)
    {
        $careplanId = encrypt_decrypt('decrypt',$careplanId);
        $diagnosisList = $this->careplan->getCareplanDiagnosis($careplanId);
        $is_careplan_detail = '';
        if(request()->is_careplan_detail)
        $is_careplan_detail = 1;
        $diagnosisHtml = view('patients.caseload.care_plan.careplan_diagnosis_list', compact('diagnosisList','is_careplan_detail'))->render();

        return $this->respond(['diagnosis_html' => $diagnosisHtml]);
    }



    public function getDiagnosisGoalDetail(Request $request)
    {

        $html = '';
        if($request->has('goal_id') && $request->has('goal_version'))
        {
            $goalId = encrypt_decrypt('decrypt',$request->get('goal_id'));
            $goalVersion = $request->get('goal_version');
            $html = $this->careplan->getDiagnosisGoalDetail($goalId, $goalVersion);
        }
        return $this->respond(['html' => $html]);

    }

    /**
     * Get Care plan Diagnosis Goal for a patient .
     * Parameter : Request
     * @return \Illuminate\Http\View
     * @throws \Throwable
     */
    public function getCareplanDiagnosisGoal(Request $request)
    {
        $html = '';
        $careplanGoals = $this->careplan->getCareplanDiagnosisGoalList($request);
        if($careplanGoals) {
            $html = view('patients.caseload.care_plan.careplan_diagnosis_goal_list', compact('careplanGoals'))->render();
        }

        return $this->respond(['html' => $html]);
    }


    /**
     * Get Care plan List for a patient .
     * Parameter : patient_id
     * @return \Illuminate\Http\View
     * @throws \Throwable
     */

    public function getCarePlanList($patient_id)
    {   

        $patient = $this->patient->patientDetail($patient_id, false);
        try{     
            $patient_id = encrypt_decrypt('decrypt',$patient_id);
        } catch (\Exception $e) {
            abort(404);
            exit;
        }

        $carePlanList = $this->careplan->getCareplanList($patient_id);
        $patient = $patient['patient_info'];
        $html = view('patients.caseload.care_plan.care_plan_list',compact('carePlanList', 'patient_id','patient'))->render();
        return response()->json(['message' => trans('message.listing_found'), 'html' => $html], 200);
    }

    
    /**
    * Get Care plan Detail for a patient .
    * Parameter : patientId,id
    * @return \Illuminate\Http\View
    */

    public function getCarePlanDetail($patientId,$id)
    {

        $patient = $this->patient->patientDetail($patientId, false);
        try{     
            $patient_id = encrypt_decrypt('decrypt',$patientId);
            $id = encrypt_decrypt('decrypt',$id);
        } catch (\Exception $e) {
            abort(404);
            exit;
        }
       $carePlanDetail = $this->careplan->getCareplanDetail($id);
       $active = 'case_manager';
       return view('patients.caseload.care_plan.care-plan-detail',compact('active', 'patient','carePlanDetail'));
    }


    /**
    * Get Care plan Detail Sectiond data on click .
    * Parameter : patientId,careplanId,Request
    * @return \Illuminate\Http\View
    */

    public function getloadTab($patientId,$careplanId,Request $request)
    {
        $dataView['data']=''; 
        try{     
            $id = encrypt_decrypt('decrypt',$careplanId);
            $patient_id = encrypt_decrypt('decrypt',$patientId);
        } catch (\Exception $e) {
            abort(404);
            exit;
        }
        if ($request->ajax()) {
            if ($request->has('tab') && $request->input('tab') == 'team') {
                $carePlanDetail = $this->careplan->getCareplanDetail($id);
                $dataView['data'] = View::make('patients.caseload.care_plan.careteam_tab')->with('carePlanDetail', $carePlanDetail)->render();
               
            }

            if ($request->has('tab') && $request->input('tab') == 'otherinfo') {
                $patient = $this->patient->patientDetail($patientId, true);
                $otherContacts = $patient['patient_other_info']['otherContacts'];
                $dataView['data'] = View::make('patients.caseload.care_plan.other_info_tab')->with('otherContacts', $otherContacts)->render();
               
            }
            if ($request->has('tab') && $request->input('tab') == 'emergency_contact') {
                $patient = $this->patient->patientDetail($patientId, false);
                $dataView['data'] = View::make('patients.caseload.care_plan.emergency_contact_info_tab')->with('patient', $patient['patient_info'])->render();
                
            }

            if ($request->has('tab') && $request->input('tab') == 'diagnosis_goals') {

                $careplanId = encrypt_decrypt('decrypt',$careplanId);
                $diagnosisList = $this->careplan->getCareplanDiagnosis($careplanId);
                $patient = $this->patient->patientDetail($patientId, false);
                $dataView['data'] = View::make('patients.caseload.care_plan.diagnosis_goal_tab')->with('diagnosisList', $diagnosisList)->render();
                
            }

            if ($request->has('tab') && $request->input('tab') == 'medication') {
                $patient = $this->patient->patientDetail($patientId, false);
                $previous_medications = $this->medication->medicationList($patient_id);
                $dataView['data'] = View::make('patients.caseload.care_plan.medication_tab')->with('previous_medications', $previous_medications)->with('patient', $patient['patient_info'])->render();
                
            }

            if ($request->has('tab') && $request->input('tab') == 'allergy') {
                $patient = $this->patient->patientDetail($patientId, false);
                $previous_allergies = $this->allergy->allergyLists($patient_id);
                $dataView['data'] = View::make('patients.caseload.care_plan.allergy_tab')->with('previous_allergies', $previous_allergies)->with('patient', $patient['patient_info'])->render();
                
            }

            if ($request->has('tab') && $request->input('tab') == 'vital') {

                $patient = $this->patient->patientDetail($patientId, false);
                $vitalList = $this->assessment->getVitalListForCareplan($careplanId, $patientId);
                $dataView['data'] = View::make('patients.caseload.care_plan.vital_tab')->with('vitalList', $vitalList)->with('patient', $patient['patient_info'])->render();
                
            }

            if ($request->has('tab') && $request->input('tab') == 'assessments') {
                 $patient = $this->patient->patientDetail($patientId, false);
                 $assessmentList = $this->assessment->getLists($patient_id,$id);
                 $dataView['data'] = View::make('patients.caseload.care_plan.assessments_tab')->with('assessmentList', $assessmentList)->with('patient', $patient['patient_info'])->with('id', $id)->render();
                
            }

            if ($request->has('tab') && $request->input('tab') == 'checkpoints') {
                $patient = $this->patient->patientDetail($patientId, false);
                $checkpointList = $this->checkpoint->getLists($patient_id,$id);
                $dataView['data'] = View::make('patients.caseload.care_plan.checkpoints_tab')->with('checkpointList', $checkpointList)->with('patient', $patient['patient_info'])->with('id', $id)->render();
                
            }

            if ($request->has('tab') && $request->input('tab') == 'barriers') {
                $patient = $this->patient->patientDetail($patientId, false);
                $barrierList = $this->assessment->getBarrierListForCareplan($careplanId, $patientId);
                $dataView['data'] = View::make('patients.caseload.care_plan.barriers_tab')->with('barrierList', $barrierList)->with('patient', $patient['patient_info'])->render();
            }

            if ($request->has('tab') && $request->input('tab') == 'intervention') {
                $patient = $this->patient->patientDetail($patientId, false);
                $interventionList = $this->intervention->getLists($patient_id,$id);
                $dataView['data'] = View::make('patients.caseload.care_plan.interventions_tab')->with('interventionList', $interventionList)->with('patient', $patient['patient_info'])->with('id', $id)->render();
            }

            if ($request->has('tab') && $request->input('tab') == 'notes') {
                $patient = $this->patient->patientDetail($patientId, false);
                $carePlanDetail = $this->careplan->getCareplanDetail($id);
                $dataView['data'] = View::make('patients.caseload.care_plan.notes_tab')->with('carePlanDetail', $carePlanDetail)->render();
            }
        }
        return  response()->json(['html'=>$dataView],200);
    }


    /**
    * Update Care plan Detail for a patient .
    * Parameter : CareplanStatusRequest
    * @return \Illuminate\Http\View
    */

    public function updateCarePlanStatus(CareplanStatusRequest $request){


        $careplan = $this->careplan->changeStatus($request);
        $message = trans('message.careplan_completed_successfully');
        try{           
            $patient_id = encrypt_decrypt('decrypt', $request->get('patient_id'));
        } catch (DecryptException $e) {
            abort(404);
            exit;
        }
        $activeCarePlan = $this->careplan->checkActiveCarePlan($patient_id);
        if($careplan->status == 2) {
            $message = trans('message.careplan_discontinued_successfully');
        }

        if($request->has('is_base_line') && $request->get('is_base_line') == '1') {
            $message = trans('message.careplan_base_line_successfully');
            $request->session()->flash('message.level','success');
            $request->session()->flash('message.content',trans('message.careplan_base_line_successfully'));
        }
        
        return $this->respond([
            'status' => 'success',
            'message'=> $message,
            'activeCarePlan' =>$activeCarePlan
        ]);
    }

    /**
    * Update Care plan Detail for a patient .
    * Parameter : CareplanStatusRequest
    * @return \Illuminate\Http\View
    */

    public function removeCareplanDiagnosis(Request $request){
        
        $delete = $this->careplan->deleteCareplanDiagnosisGoal($request);
        $status = 'success';
        if($delete){
            $message = trans('message.remove_careplan_diagnosis');
        }
        else{
            $status = 'error';
            $message = trans('message.error_remove_careplan_diagnosis');
        }
        $careplanId = encrypt_decrypt('decrypt', $request->get('careplanId'));
        $diagnosisList = $this->careplan->getCareplanDiagnosis($careplanId);
        $is_careplan_detail = '';
        $diagnosisHtml = view('patients.caseload.care_plan.careplan_diagnosis_list', compact('diagnosisList','is_careplan_detail'))->render();
        return $this->respond([
            'status' => $status,
            'message'=> $message,
            'diagnosis_html' => $diagnosisHtml
        ]);
    }
    

     /**
    * list of checkpoint for patient
    * Parameter : patient_id
    * @return \Illuminate\Http\View
    */

    public function getCheckpointList(Request $request)
    {   
        $careplan_id = 0;
        try{     
            $patient_id = encrypt_decrypt('decrypt', $request->get('patient_id'));
            if($request->has('careplan_id')){
                $careplan_id = encrypt_decrypt('decrypt', $request->get('careplan_id'));
            }

        } catch (\Exception $e) {
            abort(404);
            exit;
        }
        if(request()->is_careplan)
        $is_careplan = 1;
        else
        $is_careplan = 0;
        $patient = $this->patient->patientDetail($request->get('patient_id'), false);
        $checkpointList = $this->checkpoint->getLists($patient_id,$careplan_id);
        $patient = $patient['patient_info'];
        $html = view('patients.caseload.checkpoint.list',compact('checkpointList', 'patient_id','patient','is_careplan'))->render();       
        return response()->json(['message' => trans('message.listing_found'), 'html' => $html], 200);
    }



     /**
    * list of assessment for patient
    * Parameter : patient_id
    * @return \Illuminate\Http\View
    */

    public function getAssessmentList(Request $request)
    {   
        $careplan_id = 0;
        try{     
            $patient_id = encrypt_decrypt('decrypt', $request->get('patient_id'));
            if($request->has('careplan_id')){
                $careplan_id = encrypt_decrypt('decrypt', $request->get('careplan_id'));
            }

        } catch (\Exception $e) {
            abort(404);
            exit;
        }
        if(request()->is_careplan){
           $is_careplan = 1; 
           $activeCarePlan = 0; 
        }
        
        else{
           $is_careplan = 0;
           $activeCarePlan = $this->careplan->checkActiveCarePlan($patient_id); 
        }
        

        $patient = $this->patient->patientDetail($request->get('patient_id'), false);
        $assessmentList = $this->assessment->getLists($patient_id,$careplan_id);
        
        $patient = $patient['patient_info'];
        $html = view('patients.caseload.assessment.list',compact('assessmentList', 'patient_id','patient','is_careplan','activeCarePlan'))->render();       
        return response()->json(['message' => trans('message.listing_found'), 'html' => $html], 200);
    }

    /**
     * list of checkpoint for patient
     * Parameter : patient_id
     * @return \Illuminate\Http\View
     * @throws \Throwable
     */

    public function getInterventionList(Request $request)
    {
        $careplan_id = 0;
        try{     
            $patient_id = encrypt_decrypt('decrypt', $request->get('patient_id'));
            if($request->has('careplan_id')){
                $careplan_id = encrypt_decrypt('decrypt', $request->get('careplan_id'));
            }
        } catch (\Exception $e) {
            abort(404);
            exit;
        }
        if(request()->is_careplan)
        $is_careplan = 1;
        else
        $is_careplan = 0;

        $patient = $this->patient->patientDetail($request->get('patient_id'), false);
        $patient = $patient['patient_info'];
        $interventionList = $this->intervention->getLists($patient_id,$careplan_id);
        $html = view('patients.caseload.checkpoint.intervention.list',compact('interventionList', 'patient_id','patient','is_careplan'))->render();       
        return response()->json(['message' => trans('message.listing_found'), 'html' => $html], 200);
    }


    /**
     * Get Barriers list
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function getBarrierList(Request $request)
    {   
        $assessmentBarriers = $this->assessment->getBarrierListForCareplan($request->get('careplan_id'), $request->get('patient_id'));
        $is_careplan = true;
        $html = view('patients.caseload.assessment.barriers_list', compact('assessmentBarriers','is_careplan'))->render();

        return $this->respond([
            'html' => $html
        ]);
    }

    /**
     * Get Vitals list
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function getVitalList(Request $request)
    {   
        $vitals = $this->assessment->getVitalListForCareplan($request->get('careplan_id'), $request->get('patient_id'));
        $is_careplan = true;
        $html = view('patients.caseload.assessment.vital_list', compact('vitals'))->render();
        return $this->respond([
            'html' => $html
        ]);
    }

}   
