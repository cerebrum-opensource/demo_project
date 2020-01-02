<?php

namespace App\Models;

use App\Traits\Encryptable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;

class PatientAssessment extends Model implements Auditable
{
	use SoftDeletes;
	use \OwenIt\Auditing\Auditable;
    use Encryptable;

    public $timestamps = true;

    protected $dates = ['deleted_at'];

    protected $guarded = ['id','created_at','deleted_at','updated_at'];

    protected $encryptable = [];

    protected $dates_need_to_be_changed = ['created_at'];

    protected $nonencryptable = [
        'comment_type',
        'user_type',
        'comment'
    ]; 

    /**
	 * Get the user assigned.
	 */
	public function user()
	{
	    return $this->belongsTo('App\Models\User', 'type_id');
	}

	// customize the value that save in the audit log
    public function transformAudit(array $data): array
    {
        // check the old data exist for the audit log
        if (Arr::has($data, 'old_values.patient_id')) {
            $patientID = $data['old_values']['patient_id'];
        }
        else {
            $patientID = PatientAssessment::find($data['auditable_id'])->patient_id;     
        }

        // update the typename in log table
        if (Arr::has($data, 'old_values.type_id')) {
            $data['old_values']['type_name'] = Arr::has($data, 'old_values.type_id') && User::find($data['old_values']['type_id'])  ? User::find($data['old_values']['type_id'])->name : '';
        }
        if (Arr::has($data, 'new_values.type_id')) {
            $data['new_values']['type_name'] = Arr::has($data, 'new_values.type_id') && User::find($data['new_values']['type_id'])  ? User::find($data['new_values']['type_id'])->name : '';
        }

        Arr::set($data, 'patient_id',  $patientID);
        return $data;
    } 


    // funciton to get array of key that is not encrypted

    public function getNonEncryptableValue()
    {
        return $this->nonencryptable;
    }

    public function getAssessmentDateWithTimeAttribute()
    {
        if(!$_COOKIE['client_timezone']){
            $timezone=Config::get('app.timezone');
        }
        else{
            $timezone=$_COOKIE['client_timezone'];
        }
         $value = \Carbon\Carbon::parse($this->attributes['created_at'])->timezone($timezone)->format('m-d-Y H:i:s');

       return $value;
    }

}
