<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use App\Traits\DateFormat;
use OwenIt\Auditing\Contracts\Auditable;

class PatientMedication extends Model implements Auditable
{
	use SoftDeletes;
	use DateFormat;
	use \OwenIt\Auditing\Auditable;
    const ACTIVE = '1';
    const INACTIVE = '0';

    protected $guarded = ['id','created_at','deleted_at','updated_at'];

    protected $dates_need_to_be_changed = [
        'start_date',
        'end_date',
    ];

    protected $nonencryptable = [
        'frequency',
        'dosage',
        'name',
        'units',
        'comment',
    ];  


    public function patient()
    {
        return $this->belongsTo('App\Models\Patient');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User','type_id');
    }

    // customize the value that save in the audit log
    public function transformAudit(array $data): array
    {
        // check the old data exist for the audit log
        if (Arr::has($data, 'old_values.patient_id')) {
            $patientID = $data['old_values']['patient_id'];
        }
        else {
            $patientID = PatientMedication::find($data['auditable_id'])->patient_id;     
        }

        // update the typename in log table
        if (Arr::has($data, 'old_values.type_id')) {
            $data['old_values']['type_name'] = Arr::has($data, 'old_values.type_id') && User::find($data['old_values']['type_id'])  ? User::find($data['old_values']['type_id'])->name : '';
        }

        if (Arr::has($data, 'new_values.type_id')) {
            $data['new_values']['type_name'] = Arr::has($data, 'new_values.type_id') && User::find($data['new_values']['type_id'])  ? User::find($data['new_values']['type_id'])->name : '';
        }


        if (Arr::has($data, 'new_values.status') && !Arr::has($data, 'old_values.status')) {
            $data['new_values']['message'] = 'started';
        } else {
            if (Arr::has($data, 'new_values.status') && Arr::has($data, 'old_values.status')) {
                if($data['new_values']['status']){
                    $data['new_values']['message'] = 'restarted';
                }
                else {
                    $data['new_values']['message'] = 'discontinued';
                }
            }
            else {
                $data['new_values']['message'] = '';
            }
        }

        Arr::set($data, 'patient_id',  $patientID);
        return $data;
    } 

    public function getNonEncryptableValue()
    {
        return $this->nonencryptable;
    }

    public function getStatusBadgeAttribute()
    {
       $status='';
        switch ($this->attributes['status']) {
        case self::ACTIVE:
            $status = '<span class="badge badge-success">Active</span>';
            break;
        case self::INACTIVE:
            $status = '<span class="badge badge-danger">Inactive</span>';
            break;
        default:
            $status = '<span class="badge badge-success">Active</span>';
            break;
        }
        return $status;
    } 
}
