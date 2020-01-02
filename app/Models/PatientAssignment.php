<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;

class PatientAssignment extends Model implements Auditable
{
	use SoftDeletes;
	use \OwenIt\Auditing\Auditable;

	const TYPE_HOME_ASSIGNMENT = 0;
	const TYPE_CAREPLAN_ASSIGNMENT = 1;

    public $timestamps = true;

    protected $dates = ['deleted_at'];

    protected $guarded = ['id','created_at','deleted_at','updated_at'];

    /**
	 * Get the user assigned.
	 */
	public function user()
	{
	    return $this->belongsTo('App\Models\User', 'type_id');
	}

    protected $nonencryptable = [
        'user_type',
    ];  

	// customize the value that save in the audit log
    public function transformAudit(array $data): array
    {
        // check the old data exist for the audit log
        if (Arr::has($data, 'old_values.patient_id')) {
            $patientID = $data['old_values']['patient_id'];
        }
        else {
         $patientID = PatientAssignment::find($data['auditable_id'])->patient_id;     
        }
        // update the typename in log table
        if (Arr::has($data, 'old_values.type_id')) {
            $data['old_values']['type_name'] = Arr::has($data, 'old_values.type_id') && User::find($data['old_values']['type_id'])  ? User::find($data['old_values']['type_id'])->name : '';
        }
        // update the assigned_by_name in log table
        if (Arr::has($data, 'old_values.assigned_by')) {
            $data['old_values']['assigned_by_name'] = Arr::has($data, 'old_values.assigned_by') && User::find($data['old_values']['assigned_by'])  ? User::find($data['old_values']['assigned_by'])->name : '';
        }
        if (Arr::has($data, 'new_values.type_id')) {
            $data['new_values']['type_name'] = Arr::has($data, 'new_values.type_id') && User::find($data['new_values']['type_id'])  ? User::find($data['new_values']['type_id'])->name : '';
        }
        if (Arr::has($data, 'new_values.assigned_by')) {
            $data['new_values']['assigned_by_name'] = Arr::has($data, 'new_values.assigned_by') && User::find($data['new_values']['assigned_by'])  ? User::find($data['new_values']['assigned_by'])->name : '';
        }
        Arr::set($data, 'patient_id',  $patientID);
        return $data;
    } 

    // funciton to get array of key that is not encrypted

    public function getNonEncryptableValue()
    {
        return $this->nonencryptable;
    }

    public function scopeGetHomeAssignmentTeamByPatientId($query,$patientId)
    {
        return $query->where('patient_id', $patientId)
            ->where('assignment_type', static::TYPE_HOME_ASSIGNMENT);
    }

    public function scopeGetCareplanTeamByPatientId($query,$patientId)
    {
        return $query->where('patient_id', $patientId)
            ->where('assignment_type', static::TYPE_CAREPLAN_ASSIGNMENT);
    }

}
