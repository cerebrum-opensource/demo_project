<?php

namespace App\Services;

use App\Models\CareplanCheckpoint;
use App\Models\InterventionFollowup;
use App\Models\Intervention;
use App\Models\PatientCareplan;
use Spatie\Permission\Models\Role;

class Checkpoint
{

    /**
     * Add Purpose for checkpoint
     * @param $request
     * @return mixed
     */
    public function add($request)
    {
        $patientId = encrypt_decrypt('decrypt',$request->get('patient_id'));
        $patientCareplan = PatientCareplan::where([
            'patient_id' => $patientId,
            'status' => PatientCareplan::ACTIVE
        ])->first();

        $roleId = 0;
        if(auth()->user()->hasAnyRole(Role::all())) {
            $roleId = auth()->user()->roles->pluck('id')[0];
        }
        return CareplanCheckpoint::create([
            'careplan_id' => $patientCareplan->id,
            'patient_id'  => $patientId,
            'purpose'  => $request->get('purpose'),
            'via'  => $request->get('via'),
            'assessment_by'  =>  auth()->user()->id,
            'assessment_date'  => now(),
            'user_type'  => $roleId,
            'status'  => CareplanCheckpoint::DRAFT,
            'code'  => $this->generateCheckpointID($patientId),
        ]);
    }

    /**
     * Update purpose
     * @param $request
     * @return mixed
     */
    public function update($request)
    {
        $checkpointId = encrypt_decrypt('decrypt',$request->get('checkpoint_id'));
        $careplanAssessment = CareplanCheckpoint::findOrFail($checkpointId);

        $careplanAssessment->fill([
            'purpose' => $request->get('purpose'),
            'via' => $request->get('via')
        ])->save();

        return $careplanAssessment;

    }

    /**
     * Generate checkpoint Code Id
     * @param $patientId
     * @return string
     */
    public function generateCheckpointID($patientId)
    {   
        $patientId = 1000 + $patientId;
        return CareplanCheckpoint::CHECKPOINT_PREFIX.$patientId.today()->format('mdy');
    }

    /**
     * Add Or Update by content Discussed
     * @param $request
     * @return mixed
     */
    public function addOrUpdateContentDiscussed($request)
    {
        $checkpointId = encrypt_decrypt('decrypt',$request->get('checkpoint_id'));
        $careplanAssessment = CareplanCheckpoint::findOrFail($checkpointId);

        $careplanAssessment->fill([
            'visit_content' => $request->get('visit_content'),
            'other_notes' => $request->get('other_notes')
        ])->save();

        return $careplanAssessment;
    }

    /**
     * Update checkpoint Status
     * @param $request
     * @return mixed
     */
    public function updateCheckpointStatus($request)
    {
        $checkpointId = encrypt_decrypt('decrypt',$request->get('checkpoint_id'));
        $careplanAssessment = CareplanCheckpoint::findOrFail($checkpointId);
        $careplanAssessment->fill([
            'status' => CareplanCheckpoint::ACTIVE
        ])->save();
        return $careplanAssessment;
    }

    /**
     * Get Checkpoint list by patient and careplan id
     * @param $patientId
     * @param int $careplanId
     * @return mixed
     */
    public function getLists($patientId, $careplanId=0)
    {   
        $list = CareplanCheckpoint::where(['status' =>CareplanCheckpoint::ACTIVE,'patient_id'=>$patientId]);

        if($careplanId){
            $list = $list->where(['careplan_id' => $careplanId]);
        }
        $list = $list->with('user','careplan','userRole')->latest()->paginate(CASELOAD_PAGINATION_COUNT);
        return $list;
    }

    /**
     * Function to view checkpoint detail by checkpoint id
     * @param $checkpointId
     * @return mixed
     */
    public function getById($checkpointId)
    {
        $checkpointId = encrypt_decrypt('decrypt', $checkpointId);;
        $detail = CareplanCheckpoint::where('id',$checkpointId)->with('intervention')->first();
        return $detail;
    }

    /**
     * Delete all the partially saved checkpoints
     */
    public function deletePartialCheckpoints()
    {
        $assessmentIds = CareplanCheckpoint::where(['status'=>CareplanCheckpoint::DRAFT,'assessment_by'  =>  auth()->user()->id])->pluck('id')->toArray();
        InterventionFollowup::whereIn('assessment_id', $assessmentIds)->where('type', InterventionFollowup::TYPE_CHECKPOINT)->where('added_by', auth()->user()->id)->delete();
        Intervention::whereIn('type_id', $assessmentIds)->where('type', Intervention::TYPE_CHECKPOINT)->delete();
        CareplanCheckpoint::where(['status'=>CareplanCheckpoint::DRAFT,'assessment_by'  =>  auth()->user()->id])->delete();

        return true;

    }

}
