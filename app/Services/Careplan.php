<?php

namespace App\Services;
use App\Models\Admin\CarePlan\Diagnosis as DiagnosisModel;
use App\Models\Admin\CarePlan\DiagnosisIcdCode;
use App\Models\Admin\CarePlan\DiagnosisVersion;
use App\Models\Admin\CarePlan\GoalAssignment;
use App\Models\Admin\CarePlan\GoalVersion;
use App\Models\CareplanGoal;
use App\Repositories\CommonRepository;
use App\Models\PatientCareplan;
use App\Models\CareplanDiagnosis;

use Illuminate\Pagination\Paginator;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

Class Careplan
{
    /**
     * Add Careplan
     */
    public function addCareplan($request)
    {
        $careplanId = encrypt_decrypt('decrypt',$request->get('careplan_id'));
        $patientCareplan = PatientCareplan::find($careplanId);

        PatientCareplan::where([
            'patient_id' => $patientCareplan->patient_id,
            'status' => PatientCareplan::ACTIVE
        ])->update(['status' => PatientCareplan::ARCHIVED, 'end_date' => today()]);

        $patientCareplan->notes = $request->get('notes');
        $patientCareplan->status = PatientCareplan::ACTIVE;
        $patientCareplan->save();
        $patientCareplan->updateDiagnosisStatus();

    }

    /**
     * Assign diagnosis to patient
     */
    public function addCareplanDiagnosisGoal($request)
    {

        $roleId = 0;
        if(auth()->user()->hasAnyRole(Role::all())) {
            $roleId = auth()->user()->roles->pluck('id')[0];
        }

        $patientId = encrypt_decrypt('decrypt',$request->get('patient_id'));
        $goalIds = explode(',',$request->get('goal_ids'));

        if(!$request->get('careplan_id')) {

           $patientCareplan = PatientCareplan::create([
               'code' => $this->generateCareplanID($patientId),
               'start_date' => today()->format('Y-m-d'),
               'added_by' => auth()->user()->id,
               'user_type' => $roleId,
               'status' => PatientCareplan::PARTIALLY_SAVE,
               'patient_id' => $patientId,
           ]);

           $patientCareplan->createTeam();
           $patientCareplan->updateBaseLine();
        } else {
            $patientCareplan = PatientCareplan::find(encrypt_decrypt('decrypt', $request->get('careplan_id')));
        }


        if($request->get('edit_diagnosis_id')) { // for manage goals

            $diagnosisId = $request->get('edit_diagnosis_id');
            CareplanGoal::where('diagnosis_id', $diagnosisId)->where('careplan_id', $patientCareplan->id)->delete();


            $careplanGoalData = [];
            foreach ($goalIds as $goalId) {

                $goalData = explode('-',$goalId);
                $goalId = encrypt_decrypt('decrypt', $goalData[0]);
                $goalVersion = $goalData[1];

                $careplanGoalData[] = [
                    'goal_id' => $goalId,
                    'careplan_id' => $patientCareplan->id,
                    'diagnosis_id' => $diagnosisId,
                    'goal_version' => $goalVersion,
                    'patient_id' => $patientId,
                ];
            }

            if($careplanGoalData) {
                CareplanGoal::insert($careplanGoalData);
            }

            CareplanDiagnosis::where([
                'diagnosis_id' => $diagnosisId,
                'diagnosis_version' => $request->get('diagnosis_version'),
                'careplan_id' => $patientCareplan->id,
            ])->update([
                'goal_count' => count($goalIds),
            ]);

        } else {
            $diagnosis =  DiagnosisModel::findOrFail($request->get('diagnosis_id'));
            CareplanDiagnosis::create([
                'diagnosis_id' => $diagnosis->id,
                'careplan_id' => $patientCareplan->id,
                'diagnosis_version' => $diagnosis->current_version,
                'priority' =>$request->get('priority'),
                'date_added' => today()->format('Y-m-d'),
                'patient_id' => $patientId,
                'goal_count' => count($goalIds),
                'status' => 1,
            ]);

            foreach ($goalIds as $goalId) {
                $goalData = explode('-',$goalId);
                $goalId = encrypt_decrypt('decrypt', $goalData[0]);
                $goalVersion = $goalData[1];

                if($goalId && $goalVersion) {
                    CareplanGoal::create([
                        'goal_id' => $goalId,
                        'careplan_id' => $patientCareplan->id,
                        'diagnosis_id' => $diagnosis->id,
                        'goal_version' => $goalVersion,
                        'patient_id' => $patientId,
                    ]);
                }
            }
        }

        return $patientCareplan;

    }

    /**
     * Remove diagnosis from careplan
     */
    public function deleteCareplanDiagnosisGoal($request)
    {
        try {
            $diagnosisId = encrypt_decrypt('decrypt', $request->get('diagnosisId'));
            $careplanId = encrypt_decrypt('decrypt', $request->get('careplanId'));
        }catch(\Exception $e) {
            abort(404);
        }
        DB::beginTransaction();
        $isDeleteDiagnosis = CareplanDiagnosis::where(['careplan_id'=>$careplanId,'diagnosis_id'=>$diagnosisId])->delete();
        $isDeleteGoal = CareplanGoal::where(['careplan_id'=>$careplanId,'diagnosis_id'=>$diagnosisId])->delete();
        if($isDeleteDiagnosis && $isDeleteGoal){
            DB::commit();
            return true;
        }
        else{
            DB::rollBack();
            return false;
        }
    }

    /**
     * Get careplan list
    */
    public function getCareplanList($patient_id)
    {
        $carePlanList = PatientCareplan::where(['patient_id'=>$patient_id])->whereNotIn('status',[PatientCareplan::PARTIALLY_SAVE])->orderBy('created_at','desc')->with('user','userRole')->paginate(CASELOAD_PAGINATION_COUNT);
        return $carePlanList;
    }


    public function getDiagnosisDetail($diagnosisId, $careplanId)
    {
        $diagnosis = $this->getDiagnosisDetailByDiagnosisOrCareplanId($diagnosisId, $careplanId);
        $icdCodes = $diagnosis->icd_codes()->where('version',$diagnosis->current_version)->with('icdCodes')->get();

        return [
            'detail' => $diagnosis,
            'icdCodes' => $icdCodes
        ];

    }

    public function getCareplanDiagnosisGoalList($request)
    {
        if($request->has('careplan_id') && $request->has('diagnosis_id'))
        {
            $careplanId  = encrypt_decrypt('decrypt',$request->get('careplan_id'));
            $diagnosisId  = encrypt_decrypt('decrypt',$request->get('diagnosis_id'));

            $careplanGoals = CareplanGoal::select('careplan_goals.id as careplan_goal_id','careplan_goals.goal_id','careplan_goals.goal_version','gv.title')->where([
                'careplan_id' => $careplanId,
                'diagnosis_id' => $diagnosisId,
            ])->join('goal_versions as gv', function($join){
                $join->on('careplan_goals.goal_id','=','gv.goal_id');
                $join->on('careplan_goals.goal_version','=','gv.version');
            })->where('gv.status', GoalVersion::ACTIVE)
                ->paginate(CASELOAD_PAGINATION_COUNT)
                ->appends('careplan_id',$request->get('careplan_id'))
                ->appends('diagnosis_id',$request->get('diagnosis_id'));

            return $careplanGoals;
        }

        return false;
    }

    public function getDiagnosisGoals($diagnosisId,$careplanId)
    {
        $diagnosis = $this->getDiagnosisDetailByDiagnosisOrCareplanId($diagnosisId, $careplanId);

        $diagnosisGoals = GoalAssignment::select(
            'goal_assignments.goal_id',
            'gv.code','gv.title',
            DB::Raw('max(goal_assignments.version) as goal_version')
        )->join('goal_versions as gv', function($join) {
                $join->on('goal_assignments.version','=','gv.version');
                $join->on('goal_assignments.goal_id','=','gv.goal_id');
            })
            ->where([
                'goal_assignments.type'       => 'diagnosis',
                'goal_assignments.type_id'    => $diagnosisId,
                'goal_assignments.status'     => GoalAssignment::ACTIVE,
                'goal_assignments.deleted_at' => null,
                'gv.status' => GoalVersion::ACTIVE,
                'gv.deleted_at' => null,
            ])->groupBy('goal_id')->get()->toArray();


        if ($careplanId) {
            $diagnosisGoals = collect($diagnosisGoals);
            $selectedDiagnosisGoals = CareplanGoal::select('gv.goal_id','gv.code','gv.title','gv.version as goal_version')->where([
                'careplan_goals.careplan_id'  => $careplanId,
                'careplan_goals.diagnosis_id' => $diagnosisId,
                'careplan_goals.deleted_at' => null,
                'gv.status' => GoalVersion::ACTIVE,
                'gv.deleted_at' => null,
            ])->join('goal_versions as gv', function($join) {
                $join->on('careplan_goals.goal_version','=','gv.version');
                $join->on('careplan_goals.goal_id','=','gv.goal_id');
            })->get();

            $selectedGoalIds = $selectedDiagnosisGoals->pluck('goal_id');
            $diagnosisGoals = $diagnosisGoals->whereNotIn('goal_id', $selectedGoalIds);
            return array_sort(array_merge($selectedDiagnosisGoals->toArray(), $diagnosisGoals->toArray()));
        }
        else{
            return $diagnosisGoals;
        }

    }

    public function getDiagnosisSelectedGoalIds($diagnosisId,$careplanId)
    {
        $goalIds = [];

        $goals =CareplanGoal::where([
            'careplan_id' => $careplanId,
            'diagnosis_id' => $diagnosisId
        ])->get();

        foreach($goals as $goal) {
            $goalIds[] = encrypt_decrypt('encrypt', $goal->goal_id).'-'.$goal->goal_version;
        }

        return implode(',', $goalIds);
    }

    /**
     * Get careplan diagnosis
     */
    public function getCareplanDiagnosis($careplanId)
    {
        return CareplanDiagnosis::where('careplan_id', $careplanId)
            ->with('diagnosis')
            ->paginate(CASELOAD_PAGINATION_COUNT)
            ->withPath(url('patients/caseload/careplan/'.encrypt_decrypt('encrypt', $careplanId).'/diagnosis'));
    }

    public function getDiagnosisGoalDetail($goalId, $goalVersion)
    {
        $goal = GoalVersion::where([
            'goal_id' => $goalId,
            'version' => $goalVersion,
            'status' => GoalVersion::ACTIVE,
        ])->first();

        $subgoals = $goal->getSubgoals()
            ->paginate(CASELOAD_PAGINATION_COUNT)
            ->appends('goal_id', encrypt_decrypt('encrypt',$goalId))
            ->appends('goal_version',$goalVersion )
            ->appends('type','subgoal');

        if(request()->has('type') && request()->get('type') == 'subgoal') {
            return view('patients.caseload.care_plan.careplan_subgoal_list',compact('subgoals'))->render();
        }

        $questions = $goal->getQuestions()
            ->paginate(CASELOAD_PAGINATION_COUNT)
            ->appends('goal_id', encrypt_decrypt('encrypt',$goalId))
            ->appends('goal_version',$goalVersion )
            ->appends('type','questions');

        if(request()->has('type') && request()->get('type') == 'questions') {
            return view('patients.caseload.care_plan.careplan_question_list',compact('questions'))->render();
        }

        return view('patients.caseload.care_plan.careplan_goal_details',compact('goal','subgoals','questions'))->render();
    }


    //Function to generate Diagnosis ID
    public function generateCareplanID($patientId)
    {   
        $patientId = 1000 + $patientId;
        return PatientCareplan::CAREPLAN_PREFIX.$patientId.today()->format('mdy');
    }
    

    public function getCareplanDetail($id){
        $carePlanDetail = PatientCareplan::where('id', $id)->with('carePlanTeam')->first();
        return $carePlanDetail;
    }

    /**
     * Execute the changeStatus.
     *
     * @return array
     */
    public function changeStatus($request)
    {
        try {
            $id = encrypt_decrypt('decrypt', $request->get('id'));
        }catch(\Exception $e) {
            abort(500);
        }

        $patientCareplan = PatientCareplan::find($id);
        if( $request->get('status')){
            $patientCareplan->status = $request->get('status');
            $patientCareplan->reason = $request->get('reason');
            $patientCareplan->end_date = today()->format('Y-m-d');
        }
        if( $request->get('is_base_line')){
            $patientCareplan->is_base_line = $request->get('is_base_line');
        }
        
        $patientCareplan->save();
        return $patientCareplan;
    }

    /**
     * @param $diagnosisId
     * @param $careplanId
     * @return mixed
     */
    public function getDiagnosisDetailByDiagnosisOrCareplanId($diagnosisId, $careplanId)
    {

        if ($careplanId) {
            $careplanDiagnosis = CareplanDiagnosis::where([
                'careplan_id' => $careplanId,
                'diagnosis_id' => $diagnosisId
            ])->first();

            $diagnosis = DiagnosisVersion::where([
                'version' => $careplanDiagnosis->diagnosis_version,
                'diagnosis_id' => $diagnosisId,
                'status' => DiagnosisVersion::ACTIVE,
            ])->first();

            $diagnosis->current_version = $diagnosis->version;
            $diagnosis->priority = $careplanDiagnosis->priority;
        } else {
            $diagnosis = DiagnosisModel::findOrFail($diagnosisId);
            $diagnosis->priority = 0;
        }

        return $diagnosis;
    }


    public function checkActiveCarePlan($patientId){
        $isActive = PatientCareplan::where([
            'patient_id' => $patientId,
            'status' => PatientCareplan::ACTIVE
        ])->first();
        return $isActive;
    }
}