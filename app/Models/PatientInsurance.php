<?php

namespace App\Models;
//use App\Traits\Encryptable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Encryptable;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;

class PatientInsurance extends Model implements Auditable
{
	use SoftDeletes;
    use Encryptable;
    use \OwenIt\Auditing\Auditable;

    protected $encryptable = [
        'policy',
        'group',
        'authorized_by',
        'authorization',
    ];

    protected $dates_need_to_be_changed = [
        'expiration_date',
        'effective_date',
        'created_at',
        'updated_at'
    ];

    protected $dontThrowDecryptException = true;
    protected $table = 'patient_insurances';

    public $timestamps = true;

    protected $guarded = ['id','created_at','deleted_at','updated_at'];
    protected $dates = ['deleted_at'];

     protected $nonencryptable = [
       // 'insurance_id',
        'effective_date',
        'expiration_date',
    ];  
    
    public function patient()
    {
        return $this->belongsTo('App\Models\Patient');
    }

    public function getEffectiveDateAttribute($value)
		{

			return $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : "";
		}
    public function getExpirationDateAttribute($value)
		{

			return $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : "";
		}	

    public function getEncryptableValue()
    {
        return $this->encryptable;
    }  
    public function getNonEncryptableValue()
    {
        return $this->nonencryptable;
    }

    // customiza the value that save in the audit log
    public function transformAudit(array $data): array
    {
        
        $patientID=PatientInsurance::find($data['auditable_id'])->patient_id;  

        Arr::set($data, 'patient_id',  $patientID);

        if (Arr::has($data, 'new_values.insurance_id')) {
            $data['old_values']['insurance_name'] = $data['old_values'] && Registry::find($data['old_values']['insurance_id'])? Registry::find($data['old_values']['insurance_id'])->org_name : '';
            $data['new_values']['insurance_name'] = $data['new_values']['insurance_id'] && Registry::find($data['new_values']['insurance_id'])? Registry::find($data['new_values']['insurance_id'])->org_name : '';
        }
        return $data;
    }  

    public function insurance()
    {
        return $this->belongsTo('App\Models\Registry');
    }

}
