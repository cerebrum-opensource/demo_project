<?php

namespace App\Models;

use App\Models\Admin\CarePlan\Diagnosis;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DateFormat;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;


class CareplanDiagnosis extends Model implements Auditable
{   
    
    /* For Audit log*/
    use \OwenIt\Auditing\Auditable;
    use DateFormat;
    protected $table = 'careplan_diagnosis';
    protected $guarded = [];

    const DRAFT = '0';
    const ACTIVE = '1';
    const DEACTIVE = '2';

    protected $dates_need_to_be_changed = [
        'created_at',
        'updated_at'
    ];

    public function diagnosis()
    {
        return $this->hasOne(Diagnosis::class,'id','diagnosis_id');
    }

    public function careplanGoal()
    {
        return $this->hasMany(CareplanGoal::class,'diagnosis_id','diagnosis_id');
    }


     // customize the value that save in the audit log
    public function transformAudit(array $data): array
    {
        $patientID = CareplanDiagnosis::find($data['auditable_id'])->patient_id;  
        Arr::set($data, 'patient_id',  $patientID);
        return $data;
    } 

}
