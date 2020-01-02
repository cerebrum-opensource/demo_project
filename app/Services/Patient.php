<?php

namespace App\Services;
use Illuminate\Support\Facades\DB;
use Auth;
use App\Models\{CareplanDiagnosis,
    CareplanGoal,
    PatientCareplan,
    Patient as PatientModal,
    Registry,
    User as UserModal,
    PatientAssessment,
    PatientAssignment,
    PatientForm,
    PatientSignature};
use Carbon\Carbon;
use Storage;

class Patient extends User
{
    /**
     * Create a new user instance.
     *
     * @return void
     */
     
     
     
	 public function __construct()
	 {
	 
	 }

    /**
     * Execute the patientLists.
     *
     * @return array
     */
    public function patientLists($request,$isAjax = false)
    {
        $data = [];
        $patient = new PatientModal();

        if($isAjax){
            $patient = $patient->newQuery();
            if ($request->has('status') && $request->input('status') !='') {
                        $patient->where('enrollment_status', $request->input('status'));
                }
                if ($request->has('selected_chw') && $request->input('selected_chw') !='') {
                    $chw_id=$request->input('selected_chw');
                    $patient->whereHas('assignedChw', function ($query) use($chw_id) {
                        $query->where('user_type', '=', COMMUNITYHEALTHWORKER)->where('type_id', '=', $chw_id);
                    });
                }
                if ($request->has('selected_cm') && $request->input('selected_cm') !='') {
                    $cm_id=$request->input('selected_cm');
                    $patient->whereHas('assignedCm', function ($query) use($cm_id) {
                        $query->where('user_type', '=', CASEMANAGER)->where('type_id', '=', $cm_id);
                    });
                }
                if ($request->has('selected_md') && $request->input('selected_md') !='') {
                    $md_id=$request->input('selected_md');
                    $patient->whereHas('assignedMd', function ($query) use($md_id) {
                        $query->where('user_type', '=', MANAGERDIRECTOR)->where('type_id', '=', $md_id);
                    });
                }
                if ($request->has('refernce_number') && $request->input('refernce_number') !='') {
                        $patient->where('case_number', $request->input('refernce_number'));
                }
                if (($request->has('from_date') && $request->input('from_date') !='') && ($request->has('to_date') && $request->input('to_date') == '')) {

                        $patient->whereDate('enroll_at', '>=', change_date_format($request->input('from_date')));
                }
                if (($request->has('to_date') && $request->input('to_date') !='') && ($request->has('from_date') && $request->input('from_date') == '')) {
                        $patient->whereDate('enroll_at', '<=', change_date_format($request->input('to_date')));
                }
                if (($request->has('to_date') && $request->input('to_date') !='') && ($request->has('from_date') && $request->input('from_date') != '')) {
                     $patient->whereBetween(DB::raw('DATE(enroll_at)'), array(change_date_format($request->input('from_date')), change_date_format($request->input('to_date'))));
                    
                }
            //$patients = $patient->referral()->paginate(10);
            $data['patients'] = $patient;

        }
        else {

            $data['filter_status'] = "";
            $data['filter_from_date'] = "";
            $data['filter_to_date'] = "";
            $data['selected_cm'] = "";
            $data['selected_chw'] = "";
            $data['selected_md'] = "";
            $patient = $patient->newQuery();
            if ($request->old('status') !='') {
                $data['filter_status'] = $request->old('status');
                $patient->where('enrollment_status', $request->old('status'));
            }
            
            if (($request->old('from_date') && $request->old('from_date') !='') && ( $request->old('to_date') == '')) {
                $data['filter_from_date'] = $request->old('from_date');
                $patient->whereDate('enroll_at', '>=', $request->old('from_date'));
            }
            if (($request->old('to_date') && $request->old('to_date') !='') && ($request->old('from_date') == '')) {
                 $data['filter_from_date']  = $request->old('from_date');
                 $data['filter_to_date'] = $request->old('to_date');
                $patient->whereDate('enroll_at', '<=', $request->old('to_date'));
            }
            if (($request->old('to_date') && $request->old('to_date') !='') && ($request->old('from_date') && $request->old('from_date') != '')) {
                $data['filter_from_date']  = $request->old('from_date');
                 $data['filter_to_date'] = $request->old('to_date');
                 $patient->whereBetween(DB::raw('DATE(enroll_at)'), array($request->old('from_date'), $request->old('to_date')));
                
            }
            if ($request->old('selected_chw') !='') {
                $data['selected_chw'] = $request->old('selected_chw');
                $selected_chw = $request->old('selected_chw');
                $patient->whereHas('assignedChw', function ($query) use($selected_chw) {
                    $query->where('user_type', '=', COMMUNITYHEALTHWORKER)->where('type_id', '=', $selected_chw);
                });
            }
            if ($request->old('selected_cm') !='') {
                $data['selected_cm'] = $request->old('selected_cm');
                $selected_cm = $request->old('selected_cm');
                $patient->whereHas('assignedCm', function ($query) use($selected_cm) {
                    $query->where('user_type', '=', CASEMANAGER)->where('type_id', '=', $selected_cm);
                });
            }
            if ($request->old('selected_md') !='') {
                $data['selected_md'] = $request->old('selected_md');
                $selected_md = $request->old('selected_md');
                $patient->whereHas('assignedMd', function ($query) use($selected_md) {
                    $query->where('user_type', '=', MANAGERDIRECTOR)->where('type_id', '=', $selected_md);
                });
            }

            $request->flashOnly(['status','from_date','to_date','selected_cm','selected_md','selected_chw']);    

            $data['chw_users'] = UserModal::whereHas('roles', function ($query) {
                              $query->where('name', '=', COMMUNITYHEALTHWORKER);
                            })->where('status', USER_STATUS_ACTIVE)->orderBy('name','asc')->get()->pluck('name','id')->toArray();                
            $data['cm_users'] = UserModal::whereHas('roles', function ($query) {
                              $query->where('name', '=', CASEMANAGER);
                            })->where('status', USER_STATUS_ACTIVE)->orderBy('name','asc')->get()->pluck('name','id')->toArray();                
            $data['md_users'] = UserModal::whereHas('roles', function ($query) {
                              $query->where('name', '=', MANAGERDIRECTOR);
                            })->where('status', USER_STATUS_ACTIVE)->orderBy('name','asc')->get()->pluck('name','id')->toArray();
            $data['patients'] = $patient;
           // $patients =
        }
        return $data;
    }


    /**
     * Execute the patientDetail.
     *
     * @param $patientId
     * @param bool $otherInfo
     * @return array
     */
    public function patientDetail($patientId, $otherInfo=false)
    {
        try{
            $patient_id = encrypt_decrypt('decrypt', $patientId);
        }     
        catch (\Exception $e) {
            abort(404);
        }

        $patient = PatientModal::findOrFail($patient_id);
        $patient->calc($patient->random_key);
        $data['patient_info'] = $patient;

        // if other information is needed in patient detail
        if($otherInfo) {
            $data['patient_other_info'] = $this->patientOtherInfo($patient);
        }


        return $data;
    }


    /**
     * Execute the patientDetail.
     *
     * @param $patient
     * @return array
     */
    public function patientOtherInfo($patient)
    {

        $data['primaryInsurance'] = $patient->insurancePrimary;
        $data['secondaryInsurance'] = $patient->insuranceSecondary;

        $otherContactIds = [];
        $ids = [];
        $otherContactIds[] =  $patient->pcp_id;
        $otherContactIds[] =  $patient->rehab_information_id;
        $otherContactIds[] =  $patient->mental_health_assistance_id;
        $otherContactIds[] =  $patient->housing_assistance_id;
        $otherContactIds[] =  $patient->home_health_provider_id;
        $otherContactIds[] =  $patient->hospice_provider_id;
        if(is_array($patient->specialist_id)){
            $otherContactIds = array_merge($patient->specialist_id, $otherContactIds);
        }

        $ids = array_filter($otherContactIds);
        $data['otherContacts'] = Registry::whereIn('id', $ids)->get();
        $data['contractPayer'] = Registry::where('id', $patient->contract_payer)->first();

        return $data;
    }
    /**
     * Execute the editPatientDetail.
     *
     * @return array
     */

    // service same as savePatientInfoTabData in patient controller

    public function updatePatientDetail($request)
    {
        try {
          DB::beginTransaction();
          $imageName = '';        
          if($request->image != null && $request->upload_image_or_not == 'yes')
          {
              $imageName = time().'.'.request()->image->getClientOriginalExtension();
          }
          $patient_id = null;
          try{
            $patient_id = encrypt_decrypt('decrypt', $request->patient_id); 
          }     
          catch (\Exception $e) {
              abort(404);
              exit;
          }
          $patient = PatientModal::find($patient_id);
          $patient->calc($patient->random_key);
          $dob  = change_date_format($request->dob);
          $request->request->add(['dob'=>$dob]);
          
          $patient_record = $patient->fill($request->except('_token','step_number','action','patient_id','upload_image_or_not', 'assignment_modal', 'message_type'));
          if($request->upload_image_or_not == 'yes')
              $patient_record->image = $imageName;
          $patient_record->save();
        //  $patient_id = $request->patient_id;
          if($request->image != null && $request->upload_image_or_not == 'yes')
          {
              Storage::disk('s3')->put(config('filesystems.s3_patient_images_partial_path').$patient_id.'/'.$imageName, file_get_contents($request->file('image')),'public');
          }
          DB::commit();
          return $patient_id;
        } 
        catch(\Illuminate\Database\QueryException $ex){ 
          return 0;
        }
        
    }

    /**
     * Execute the addCareplanTeam.
     *
     * @return array
     */
    public function addCareplanTeam($patientId,$request)
    {

        
    }

     /**
     * Execute the editCareplanTeam.
     *
     * @return array
     */
    public function editCareplanTeam($request)
    {
        $patient_id = encrypt_decrypt('decrypt', $request->patient_id);
        $oldAssignments = PatientAssignment::getCareplanTeamByPatientId($patient_id)->get();

        if($oldAssignments->count()) {
            foreach($oldAssignments as $assignment) {

                if($assignment->user_type === COMMUNITYHEALTHWORKER && $assignment->type_id != $request->assigned_chw)
                {
                    $assignment->delete();
                    $this->createAssignment($request->assigned_chw, $patient_id, COMMUNITYHEALTHWORKER);
                }

                if($assignment->user_type === CASEMANAGER && $assignment->type_id != $request->assigned_cm)
                {
                    $assignment->delete();
                    $this->createAssignment($request->assigned_cm, $patient_id, CASEMANAGER);
                }

                if($assignment->user_type === MANAGERDIRECTOR && $assignment->type_id != $request->assigned_md)
                {
                    $assignment->delete();
                    $this->createAssignment($request->assigned_md, $patient_id, MANAGERDIRECTOR);
                }
            }

            $patientCareplan = PatientCareplan::where('patient_id', $patient_id)->where('status', PatientCareplan::ACTIVE)->first();
            if($patientCareplan) {
                $patientCareplan->updateTeam();
            }


        } else {
            $this->createAssignment($request->assigned_chw, $patient_id, COMMUNITYHEALTHWORKER);
            $this->createAssignment($request->assigned_cm, $patient_id, CASEMANAGER);
            $this->createAssignment($request->assigned_md, $patient_id, MANAGERDIRECTOR);
        }

        return true;
    }

     /**
     * Execute the editCareplanTeam.
     *
     * @return array
     */
    public function getAssessmentsNotesList($patient_id,$user_type,$request)
    {
      try{           
          $patient_id = encrypt_decrypt('decrypt', $patient_id); 
          $user_type = encrypt_decrypt('decrypt', $user_type);
        } catch (\Exception $e) {
          abort(404);
          exit;
        }

        $patient = PatientModal::findOrFail($patient_id);
        $assessmentsNotesList = PatientAssessment::where(['user_type'=>$user_type,'patient_id'=>$patient_id])->where('comment_type','!=','consent_rejection')->get();
        $data['user_type'] = $user_type;
        $data['assessments'] = $assessmentsNotesList;
        return $data;
    }


    /**
     * Execute the editCareplanTeam.
     *
     * @return array
     */
    public function getPatientConsentForm($patient_id)
    {
        
        $patient = PatientModal::with('consentSignature','consentFormData')->findOrFail($patient_id);
       
        $patient_consent_form_data = [];


        if(count($patient->consentFormData)){
          $patient_consent_form_data = json_decode($patient->consentFormData[0]->form_data, true); 
          $patient_consent_form_data['authorize_by'] = $patient->consentFormData[0]->authorize ?  $patient->consentFormData[0]->authorize->name : '';
          
        }
        if(count($patient->consentSignature))
            $patient_consent_form_data['signature']  = $patient->consentSignature[0]->signature;
            
       /* $data = PatientForm::where(function($q) use($patient_id){ 
                              $q->orWhere('type','0')
                              ->orWhere('type','1');
                              })->where('patient_id',$patient_id)->get();*/
        return $patient_consent_form_data;
    }


    public function getPatientHippaForm($patient_id)
    {
      $patient = PatientModal::with('hippaSignature','hippaFormData')->findOrFail($patient_id);
      $patient_hippa_form_data = [];

      if(count($patient->hippaFormData)){
        $patient_hippa_form_data = json_decode($patient->hippaFormData[0]->form_data, true); 
        $patient_hippa_form_data['authorize_by'] = $patient->hippaFormData[0]->authorize ?  $patient->hippaFormData[0]->authorize->name : '';
      }
      if(count($patient->hippaSignature))
            $patient_hippa_form_data['signature']  = $patient->hippaSignature[0]->signature;
        return $patient_hippa_form_data;
    }


    private function createAssignment($typeId, $patientId, $userType): void
    {
        PatientAssignment::create([
            'user_type' => $userType,
            'patient_id' => $patientId,
            'type_id' => $typeId,
            'assigned_by' => auth()->user()->id,
            'assignment_type' => PatientAssignment::TYPE_CAREPLAN_ASSIGNMENT,
        ]);
    }


    public function savePatientReleaseForm($request,$form_type)
    {
        try{           
            $patient_id = \Crypt::decrypt($request->patient_id);
        } catch (DecryptException $e) {
            abort(404);
            exit;
        }

       if($request->consent_form_date){
            $request->request->add(['consent_form_date' => Carbon::now()->toDateTimeString()]);
        }
         if($request->consent_form_signature_date){
            $request->request->add(['consent_form_signature_date'=>Carbon::now()->toDateTimeString()]);
        }
        if($request->consent_form_signature_setup){
            $request->request->add(['consent_form_signed'=>1]);
        }

        if($request->hippa_form_date){
            $request->request->add(['hippa_form_date' => Carbon::now()->toDateTimeString()]);
        }
        if($request->hippa_form_signature_date){
            $request->request->add(['hippa_form_signature_date'=>Carbon::now()->toDateTimeString()]);
        }

        if($request->hippa_validation_date){
            $request->request->add(['hippa_validation_date'=>Carbon::now()->toDateTimeString()]);
        }
        
        
        if($request->hippa_form_signature_setup){
            $request->request->add(['hippa_form_signed'=>1]);
        }
        
        $save_signature = PatientModal::find($patient_id);
        $request->request->add(['patient_decision' => 'accepts']);
        
        $signedForm['patient_id'] = $patient_id;
        if($form_type == 0){
          $signedForm['signature'] = $request->consent_form_signature_setup ? $request->consent_form_signature_setup : '';
        }

        if($form_type == 1){
          $signedForm['signature'] = $request->hippa_form_signature_setup ? $request->hippa_form_signature_setup : '';
        }
       
        
        $signedForm['type'] = $form_type;

        $signData = $request->except(['patient_id','_token','consent_form_signed','consent_form_signature_setup','patient_decision','hippa_form_signature_setup','form_type']);
        $signFormData['authorize_by'] = Auth::user()->id;
        $signFormData['patient_id'] = $patient_id;
        $signFormData['type'] = $form_type;
        $signFormData['form_data'] = json_encode($signData);

        PatientSignature::create($signedForm);
        
        $updatePatientData = $request->except(['patient_id','consent_form_language','acknowledge_receive_services','acknowledge_emergency_medical_services','acknowledge_release_medical_records','acknowledge_release_vehicle','acknowledge_patient_bill_of_rights','consent_form_living_will_executed','consent_form_dpoa_executed','consent_form_dpoa_name','consent_form_dpoa_phone_number','consent_form_signature_date','consent_form_date','consent_form_documents_located_at_with','acknowledge_signature','consent_form_patient_initials','consent_form_signature_setup','hippa_form_language','hippa_form_signature_setup','acknowledge_authorize_person','acknowledge_description_of_info','acknowledge_purpose_to_use','acknowledge_validity_of_form','hippa_form_signature_date','hippa_validation_date','hippa_form_date','form_type']);
        PatientForm::create($signFormData);
        $save_signature = $save_signature->update($updatePatientData);
        return $save_signature;
    }


    public function deletePartialRecords($patientId)
    {   
        try{
            $patient_id = encrypt_decrypt('decrypt', $patientId);
        }     
        catch (\Exception $e) {
            abort(404);
        }

        $careplanId = PatientCareplan::where(['status'=>PatientCareplan::PARTIALLY_SAVE,'added_by'  =>  auth()->user()->id,'patient_id'=>$patient_id])->pluck('id')->toArray();
        CareplanDiagnosis::whereIn('careplan_id', $careplanId)->where("status", CareplanDiagnosis::DRAFT)->forceDelete();
        CareplanGoal::whereIn('careplan_id', $careplanId)->forceDelete();
        PatientCareplan::where(['status'=>PatientCareplan::PARTIALLY_SAVE,'added_by'  =>  auth()->user()->id,'patient_id'=>$patient_id])->forceDelete();
    }
}
