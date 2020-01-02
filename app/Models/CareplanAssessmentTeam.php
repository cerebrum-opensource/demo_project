<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;

class CareplanAssessmentTeam extends Model implements Auditable
{   

     /* For Audit log*/
    use \OwenIt\Auditing\Auditable;
    protected $table = 'careplan_assessment_team';
    protected $guarded = [];



    public function chwUser()
    {
        return $this->belongsTo('App\Models\User','chw_id');
    }

    public function mdUser()
    {
        return $this->belongsTo('App\Models\User','md_id');
    }

    public function cmUser()
    {
        return $this->belongsTo('App\Models\User','cm_id');
    }

     // customize the value that save in the audit log
    public function transformAudit(array $data): array
    {
        $patientID = CareplanAssessmentTeam::find($data['auditable_id'])->patient_id;  
        Arr::set($data, 'patient_id',  $patientID);
        return $data;
    } 
}
