<?php

namespace App\Models;

use App\Models\Admin\CarePlan\GoalAssignment;
use App\Models\Admin\CarePlan\GoalVersion;
use App\Traits\DateFormat;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;

class AssessmentGoal extends Model implements Auditable
{
    
     /* For Audit log*/
    use \OwenIt\Auditing\Auditable;
    use DateFormat;
    protected $guarded = [];
    protected $table = 'assessment_goals';

    const DRAFT = '0';
    const ACTIVE = '1';


    protected $dates_need_to_be_changed = [
        'created_at',
        'updated_at'
    ];

    public function goalVersions()
    {
        return $this->hasOne(GoalVersion::class, 'goal_id', 'goal_id');
    }

    public function getGoalAssignments()
    {
        return GoalAssignment::where('goal_id', $this->goal_id)
            ->where('type', 'question')
            ->where('version', $this->goal_version)
            ->with('question')->get();
    }

     // customize the value that save in the audit log
    public function transformAudit(array $data): array
    {
        $patientID = AssessmentGoal::find($data['auditable_id'])->patient_id;  
        Arr::set($data, 'patient_id',  $patientID);
        return $data;
    } 
}
