<?php

namespace App\Models;

use App\Traits\DateFormat;
use Illuminate\Database\Eloquent\Model;

use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;

class Intervention extends Model implements Auditable
{
    //

    use \OwenIt\Auditing\Auditable;


    use DateFormat;
    protected $guarded = [];

    const DRAFT = '0';
    const ACTIVE = '1';
    const DEACTIVE = '2';

    const TYPE_ASSESSMENT = 0;
    const TYPE_CHECKPOINT = 1;

    protected $dates_need_to_be_changed = [
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'assigned_users' => 'array',
    ];


    public function user()
    {
        return $this->belongsTo('App\Models\User','added_by');
    }

    public function careplan()
    {
        return $this->belongsTo('App\Models\PatientCareplan','careplan_id');
    }

    public function userRole()
    {
       return $this->belongsTo('Spatie\Permission\Models\Role','user_type');
    }


    public function checkpoint()
    {
       return $this->hasOne('App\Models\CareplanCheckpoint','id', 'type_id');
    }

    public function assessment()
    {
       return $this->hasOne('App\Models\CareplanAssessment','id', 'type_id');
    }

    public function getFlagNameAttribute($value)
    {
        $status='';
        if($this->attributes['flag']){
            return '<i class="fas fa-flag '.$this->attributes['flag'].'-flag"></i>';
        }
        else{
            return '<i class="far fa-flag show-flag -flag"></i>';
        }

    }


     // customize the value that save in the audit log
    public function transformAudit(array $data): array
    {
        $patientID = Intervention::find($data['auditable_id'])->patient_id;  
        Arr::set($data, 'patient_id',  $patientID);
        return $data;
    } 
}
