<?php

namespace App\Models;

use App\Traits\DateFormat;
use Illuminate\Database\Eloquent\Model;

use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;

class CareplanAssessment extends Model implements Auditable
{
    
     /* For Audit log*/
    use \OwenIt\Auditing\Auditable;
    protected $appends = ['assessment_date_time'];
    use DateFormat;
    protected $guarded = [];

    const DRAFT = '0';
    const ACTIVE = '1';
    const DEACTIVE = '2';

     const ASSESSMENT_PREFIX = 'WA';

    protected $dates_need_to_be_changed = [
        'created_at',
        'updated_at',
        'assessment_date',
        'assessment_date_time',
    ];

    protected $casts = [
        'visit_content' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User','assessment_by');
    }

    public function careplan()
    {
        return $this->belongsTo('App\Models\PatientCareplan','careplan_id');
    }

    public function userRole()
    {
        return $this->belongsTo('Spatie\Permission\Models\Role','user_type');
    }

    public function intervention()
    {
        return $this->hasOne('App\Models\Intervention','type_id', 'id')->where('type', 0);
    }

    public function interventionFollowups()
    {
        return $this->hasMany(InterventionFollowup::class, 'assessment_id', 'id')->where('type', 0);
    }

    public function getGoalReview()
    {
        return $this->hasMany(CareplanAssessmentGoalReview::class, 'assessment_id', 'id');
    }

    public function getAssessmentGoals()
    {
        return $this->hasMany(AssessmentGoal::class, 'assessment_id', 'id');
    }


     // customize the value that save in the audit log
    public function transformAudit(array $data): array
    {
        $patientID = CareplanAssessment::find($data['auditable_id'])->patient_id;  
        Arr::set($data, 'patient_id',  $patientID);
        return $data;
    } 

    public function getAssessmentDateTimeAttribute($value)
    {   
        return $this->attributes['assessment_date'];
    }
}
