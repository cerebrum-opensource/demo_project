<?php

namespace App\Models;

use App\Traits\DateFormat;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;


class CareplanAssessmentGoalReview extends Model implements Auditable
{

    /* For Audit log*/
    use \OwenIt\Auditing\Auditable;
    use DateFormat;
    protected $guarded = [];
    protected $table = 'assessment_goal_review';

    const DRAFT = '0';
    const ACTIVE = '1';


    protected $dates_need_to_be_changed = [
        'created_at',
        'updated_at'
    ];

    public function metric()
    {
        return $this->hasOne('App\Models\ManageableField','id', 'metric_id');
    }


    public function getAssessmentGoal()
    {
        return AssessmentGoal::select('assessment_id','diagnosis_id','title','patient_id','assessment_goals.goal_id','assessment_goals.goal_version','flag')
            ->where('assessment_id', $this->assessment_id)
            ->where('diagnosis_id', $this->diagnosis_id)
            ->where('patient_id', $this->patient_id)
            ->join('goal_versions as gv', function($join){
                $join->on('assessment_goals.goal_id','=', 'gv.goal_id');
                $join->on('assessment_goals.goal_version','=', 'gv.version');
            })->get();
    }

    public function getGoalReviewData()
    {
        return $this->hasMany(CareplanAssessmentGoalReviewData::class, 'assessment_goal_review_id', 'id');
    }

    // customize the value that save in the audit log
    public function transformAudit(array $data): array
    {   
        $patientID = CareplanAssessmentGoalReview::find($data['auditable_id']) ? CareplanAssessmentGoalReview::find($data['auditable_id'])->patient_id : '';
      //  $patientID = ;  
        Arr::set($data, 'patient_id',  $patientID);
        return $data;
    } 


}
