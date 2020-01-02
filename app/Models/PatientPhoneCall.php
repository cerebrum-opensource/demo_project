<?php

namespace App\Models;
//use App\Traits\Encryptable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use App\Traits\Encryptable;
use Illuminate\Support\Arr;
use App\Traits\DateFormat;

class PatientPhoneCall extends Model implements Auditable
{
    use SoftDeletes;
	use DateFormat;
	use \OwenIt\Auditing\Auditable;

    protected $dates_need_to_be_changed = [
        'contact_date',
        'updated_at',
        'assessment_date'
    ];
    
    protected $table = 'patient_phone_calls';

    public $timestamps = true;

    protected $guarded = ['id','created_at','deleted_at','updated_at'];
    protected $dates = ['deleted_at'];


    protected $nonencryptable = [
        'contact_date',
        'contact_time',
        'agree',
        'comment',
        'location'
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
        $patientID=PatientPhoneCall::find($data['auditable_id'])->patient_id;  
        Arr::set($data, 'patient_id',  $patientID);
        return $data;
    } 

    public function getCreatedTimeAttribute()
    {
       if(!$_COOKIE['client_timezone']){
                $timezone=Config::get('app.timezone');
        }
        else {
                $timezone=$_COOKIE['client_timezone'];
        }
        $valueDate = \Carbon\Carbon::parse($this->created_at)->timezone($timezone)->format('m-d-Y H:i:s');
        return $valueDate;    
    } 


    public function getNonEncryptableValue()
    {
        return $this->nonencryptable;
    }
}
