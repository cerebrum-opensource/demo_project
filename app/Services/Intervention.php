<?php

namespace App\Services;

use App\Models\{PatientCareplan,
    Intervention as InterventionModal,
    InterventionFollowup,
    User as UserModal,
    CareplanAssessmentTeam};
use Spatie\Permission\Models\Role;

class Intervention
{
    /**
     * Add Intervention Followup
     * @param $request
     * @param int $type
     * @return mixed
     */
    public function addFollowUp($request, $type=Intervention::TYPE_ASSESSMENT)
    {
        $patientId = encrypt_decrypt('decrypt',$request->get('patient_id'));

       if($request->has('id') && $request->get('id')) {
            $assessmentId = encrypt_decrypt('decrypt', $request->get('id'));
        }

        if($request->has('assessment_id') && $request->get('assessment_id')) {
            $assessmentId = encrypt_decrypt('decrypt', $request->get('assessment_id'));
        }

       	$roleId = 0;
        if(auth()->user()->hasAnyRole(Role::all())) {
            $roleId = auth()->user()->roles->pluck('id')[0];
        }
        return InterventionFollowup::create([
            'assessment_id' => $assessmentId,
            'type'  => $type,
            'follow_up_item'  => $request->get('follow_up_item'),
            'follow_up_date'  => change_date_format($request->get('follow_up_date')),
            'status'  => InterventionFollowup::DRAFT,
            'added_date'  => now(),
            'added_by'  => auth()->user()->id,
            'user_type' => $roleId  
        ]);

    }

    /**
     * Get intervention Followup list by assessment Id
     * @param $id
     * @param int $type
     * @return mixed
     */
    public function getFollowUpList($id, $type=Intervention::TYPE_ASSESSMENT)
    {
        return InterventionFollowup::where([
            'assessment_id' => $id,
            'type'  => $type
        ])->paginate(CASELOAD_PAGINATION_COUNT);

    }

     /**
     * Get Active intervention Followup list by assessment Id
     * @param $id
     * @param int $type
     * @return mixed
     */
    public function getActiveFollowUpList($id, $type=Intervention::TYPE_ASSESSMENT)
    {
        return InterventionFollowup::where([
            'assessment_id' => $id,
            'type'  => $type,
            'status' =>InterventionFollowup::ACTIVE
        ])->paginate(CASELOAD_PAGINATION_COUNT);

    }
    /**
     * Add Intervention
     * @param $request
     * @return mixed
     */
    public function add($request, $type=Intervention::TYPE_ASSESSMENT)
    {
        $patientId = encrypt_decrypt('decrypt',$request->get('patient_id'));

        if($request->has('id') && $request->get('id')) {
            $assessmentId = encrypt_decrypt('decrypt', $request->get('id'));
        }

        if($request->has('assessment_id') && $request->get('assessment_id')) {
            $assessmentId = encrypt_decrypt('decrypt', $request->get('assessment_id'));
        }
        $careplanId = encrypt_decrypt('decrypt',$request->get('careplan_id'));

        $type = $request->get('type');
        $roleId = 0;
        if(auth()->user()->hasAnyRole(Role::all())) {
            $roleId = auth()->user()->roles->pluck('id')[0];
        }   
        InterventionFollowup::where(['assessment_id' => $assessmentId,
            'type'  => $type])->update([
            'status' => InterventionFollowup::ACTIVE
        ]);

        return InterventionModal::create([
            'type_id' => $assessmentId,
            'careplan_id' => $careplanId,
            'patient_id'  => $patientId,
            'type'  => $type,
            'flag'  => $request->get('flag'),
            'summary'  => $request->get('summary'),
            'action'  => $request->get('action'),
            'assigned_users'  => $request->get('assigned_users'),
            'added_date'  => now(),
            'added_by'  => auth()->user()->id,
            'user_type' => $roleId  
        ]);
    }

    /**
     * Update Intervention
     * @param $request
     * @return mixed
     */
    public function update($request, $type=Intervention::TYPE_ASSESSMENT)
    {
        if($request->has('id') && $request->get('id')) {
            $assessmentId = encrypt_decrypt('decrypt', $request->get('id'));
        }

        if($request->has('assessment_id') && $request->get('assessment_id')) {
            $assessmentId = encrypt_decrypt('decrypt', $request->get('assessment_id'));
        }

        $interventionId = encrypt_decrypt('decrypt',$request->get('intervention_id'));
        $careplanAssessment = InterventionModal::findOrFail($interventionId);

        $careplanAssessment->fill([
            'flag'  => $request->get('flag'),
            'summary'  => $request->get('summary'),
            'action'  => $request->get('action'),
            'assigned_users'  => $request->get('assigned_users'),
        ])->save();

        InterventionFollowup::where(['assessment_id' => $assessmentId,
            'type'  => $type])->update([
                'status' => InterventionFollowup::ACTIVE
        ]);
        
        
        return $careplanAssessment;

    }

    /**
     * Function to get intervention list by patient and careplan Id
     * @param $patientId
     * @param int $careplanId
     * @return mixed
     */
    public function getLists($patientId, $careplanId=0)
    {   
       $list = InterventionModal::where(['patient_id'=>$patientId]);

       if($careplanId){
            $list = $list->where(['careplan_id' =>$careplanId]);
        }

        $list = $list->with('user','careplan','userRole')->latest()->paginate(CASELOAD_PAGINATION_COUNT);

       return $list;
    }

    /**
     * Update followup complete status
     * @param $followUpId
     * @return bool
     */
    public function updateFollowupCompleteStatus($followUpId)
    {
        $followUp = InterventionFollowup::findOrFail($followUpId);
        $followUp->is_completed = !$followUp->is_completed;
        $followUp->save();

        return true;
    }

    /**
     * Get Intervention By Id
     * @param $interventionId
     * @return mixed
     */
    public function getById($interventionId)
    {
        try{           
            $interventionId = encrypt_decrypt('decrypt', $interventionId);;
        } catch (DecryptException $e) {
            abort(404);
            exit;
        }

         $intervention = InterventionModal::findOrFail($interventionId);

         $interventionFollowUps = $this->getFollowUpList($intervention->type_id,$intervention->type);
         $careTeam = UserModal::where('id', $intervention->assigned_users)->pluck('name','id');
         $data['intervention'] =  $intervention;
         $data['interventionFollowUps'] =  $interventionFollowUps;
         $data['careTeam'] =  $careTeam;
         return $data;
    }
}
