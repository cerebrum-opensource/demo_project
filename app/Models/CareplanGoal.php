<?php

namespace App\Models;

use App\Models\Admin\CarePlan\GoalAssignment;
use App\Models\Admin\CarePlan\SubGoal;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;

class CareplanGoal extends Model implements Auditable
{	

	/* For Audit log*/
    use \OwenIt\Auditing\Auditable;
    protected $guarded = [];



      // customize the value that save in the audit log
    public function transformAudit(array $data): array
    {
        $patientID = CareplanGoal::find($data['auditable_id'])->patient_id;  
        Arr::set($data, 'patient_id',  $patientID);
        return $data;
    }

    public function getUpdatedDate()
    {
        $assessmentGoal = AssessmentGoal::where([
            'diagnosis_id' => $this->diagnosis_id,
            'goal_id' => $this->goal_id,
            'goal_version' => $this->goal_version,
            'patient_id' => $this->patient_id
        ])->latest()->first();

        if ($assessmentGoal) {
            return $assessmentGoal->created_at;
        }

        return 'N/A';
    }

    public function questions() {
        $roleId = 0;
        if(auth()->user()->roles) {
            $roleId = auth()->user()->roles[0]->id;
        }

        return $this->belongsToMany('App\Models\Admin\CarePlan\Question', 'goal_assignments', 'goal_id', 'type_id','goal_id')
            ->where('type','question')
            ->where('goal_assignments.version',$this->goal_version)
            ->where('assigned_roles','like',"[%$roleId%]")
           // ->whereJsonContains('assigned_roles',"{$roleId}")
            ->whereNull('goal_assignments.deleted_at');
    }
}
