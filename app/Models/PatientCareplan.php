<?php

namespace App\Models;

use App\Traits\DateFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;


class PatientCareplan extends Model implements Auditable
{

    /* For Audit log*/
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    use DateFormat;

    protected $table = 'patients_careplan';

    protected $guarded = [];

    const CAREPLAN_PREFIX = 'CP';

    const PARTIALLY_SAVE = '0';
    const ACTIVE = '1';
    const DISCOUNTINUE = '2';
    const COMPLETED = '3';
    const ARCHIVED = '4';


    const BASELINE_ACTIVE = 1;
    const BASELINE_INACTIVE = 0;

    protected $dates_need_to_be_changed = [
        'start_date',
        'end_date'
    ];


    public function createTeam()
    {
        $hasTeam = CareplanAssessmentTeam::where('careplan_id', $this->id)->where('patient_id', $this->patient_id)->count();
        if(!$hasTeam) {
            $assignments = PatientAssignment::where('patient_id', $this->patient_id)->where('assignment_type', PatientAssignment::TYPE_CAREPLAN_ASSIGNMENT)->pluck('type_id','user_type')->toArray();
            CareplanAssessmentTeam::create([
                'chw_id' => $assignments[COMMUNITYHEALTHWORKER],
                'md_id' => $assignments[MANAGERDIRECTOR],
                'cm_id' => $assignments[CASEMANAGER],
                'patient_id' => $this->patient_id,
                'careplan_id' => $this->id
            ]);
        }
    }

    public function updateTeam()
    {
        $assignments = PatientAssignment::where('patient_id', $this->patient_id)->where('assignment_type', PatientAssignment::TYPE_CAREPLAN_ASSIGNMENT)->pluck('type_id','user_type')->toArray();
        CareplanAssessmentTeam::where([
            'patient_id' => $this->patient_id,
            'careplan_id' => $this->id
        ])->update([
            'chw_id' => $assignments[COMMUNITYHEALTHWORKER],
            'md_id' => $assignments[MANAGERDIRECTOR],
            'cm_id' => $assignments[CASEMANAGER]
        ]);
    }

    public function updateBaseLine()
    {
        $hasCareplan = static::where('patient_id', $this->patient_id)->where('status', '!=', static::PARTIALLY_SAVE)->count();
        if ($hasCareplan) {
            $this->is_base_line = static::BASELINE_INACTIVE;
        } else {
            $this->is_base_line = static::BASELINE_ACTIVE;
        }

        $this->save();
    }

    public function updateDiagnosisStatus()
    {

        CareplanDiagnosis::where('careplan_id', $this->id)->update([
            'status' => CareplanDiagnosis::ACTIVE
        ]);
    }


    public function user()
    {
        return $this->belongsTo('App\Models\User','added_by');
    }

    public function userRole()
    {
        return $this->belongsTo('Spatie\Permission\Models\Role','user_type');
    }


    public function carePlanTeam()
    {
        return $this->hasOne('App\Models\CareplanAssessmentTeam','careplan_id');
    }


    public function getStatusBadgeAttribute()
    {
       $status='';
        switch ($this->attributes['status']) {
        case PatientCareplan::ACTIVE:
            $status = '<span class="badge badge-secondary">Active</span>';
            break;
        case PatientCareplan::DISCOUNTINUE:
            $status = '<span class="badge badge-danger">Discountinued</span>';
            break;
        case PatientCareplan::COMPLETED:
            $status = '<span class="badge badge-success">Completed</span>';
            break;
        case PatientCareplan::ARCHIVED:
            $status = '<span class="badge badge-warning">Archived</span>';
            break;
        default:
            $status = '<span class="badge badge-secondary">Active</span>';
            break;
        }
        return $status;
    }


    public function diagnosis()
    {
        return $this->hasMany(CareplanDiagnosis::class,'careplan_id');
    }

    // customize the value that save in the audit log
    public function transformAudit(array $data): array
    {
        $patientID = PatientCareplan::find($data['auditable_id'])->patient_id;  
        Arr::set($data, 'patient_id',  $patientID);
        return $data;
    } 

}
