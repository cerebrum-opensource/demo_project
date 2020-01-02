<?php

namespace App\Models;
//use App\Traits\Encryptable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use App\Traits\Encryptable;
use Illuminate\Support\Arr;

class PatientData extends Model implements Auditable
{
	use SoftDeletes;
	use \OwenIt\Auditing\Auditable;
	use Encryptable;

    protected $dontThrowDecryptException = true;

	protected $encryptable = [
        'name',
        'value',
    ];

    protected $dates_need_to_be_changed = [
        'dob',
        'created_at',
        'updated_at'
    ];
    
    protected $table = 'patient_data';

    public $timestamps = true;

    protected $guarded = ['id','created_at','deleted_at','updated_at'];
    protected $dates = ['deleted_at'];
    
    public function patient()
    {
        return $this->belongsTo('App\Models\Patient');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function document_category()
    {
        return $this->belongsTo('App\Models\ManageableField','category_id');
    }

     public function getEncryptableValue()
    {
        return $this->encryptable;
    }

    // customiza the value that save in the audit log
    public function transformAudit(array $data): array
    {   

       
        if (Arr::has($data, 'old_values.patient_id')) {
            $patientID = $data['old_values']['patient_id'];
        }
        else {
         $patientID = PatientData::find($data['auditable_id'])->patient_id;     
        } 
        if (Arr::has($data, 'old_values.deleted_by')) {
            $patientID = $data['old_values']['patient_id'];
            $data['old_values']['deleted_by_name'] = Arr::has($data, 'old_values.patient_id') && User::find($data['old_values']['deleted_by'])  ? User::find($data['old_values']['deleted_by'])->name : '';
        }
        Arr::set($data, 'patient_id',  $patientID);
        return $data;
    } 
}
