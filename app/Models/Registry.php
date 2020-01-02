<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\DateFormat;
use Illuminate\Database\Eloquent\SoftDeletes;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

class Registry extends Model
{
    //
    // use Cachable;
    use DateFormat;
    use SoftDeletes;
    public $timestamps = true;
    protected $dates = ['deleted_at'];

    protected $guarded = ['id','created_at','deleted_at','updated_at'];

    protected $dates_need_to_be_changed = [
        'effective_start_date',
        'effective_end_date',
        'created_at',
        'updated_at'
    ];

    protected $appends = ['state_name'];

    public function getStateNameAttribute($value)
    {
       $state =  $this->state;	
       return $state->full_name;	
    } 

    public function state()
    {
        return $this->belongsTo('App\Models\State');
    }

    public function scopeInsurance($query)
    { 
        return $query->where('type', 'insurances')->with('state')->orderBy('created_at','desc');
    } 

    public function scopeEmergencyDepartment($query)
    { 
        return $query->where('type', 'emergency_departments')->with('state')->orderBy('created_at','desc');
    } 

    public function scopeRehabInformation($query)
    { 
        return $query->where('type', 'rehabs')->with('state')->orderBy('created_at','desc');
    } 
    public function scopeHospiceProvider($query)
    { 
        return $query->where('type', 'hospice_providers')->with('state')->orderBy('created_at','desc');
    } 
    public function scopeHousingAssistance($query)
    { 
        return $query->where('type', 'housing_assistances')->with('state')->orderBy('created_at','desc');
    } 
    public function scopeMentalHealthAssistance($query)
    { 
        return $query->where('type', 'mental_health_assistances')->with('state')->orderBy('created_at','desc');
    } 
    public function scopeHomeHealthProvider($query)
    { 
        return $query->where('type', 'home_health_providers')->with('state')->orderBy('created_at','desc');
    }

    public function scopePcpInformation($query)
    { 
        return $query->where('type', 'pcp_informations')->with('state')->orderBy('created_at','desc');
    }  

    public function scopeRegistry($query,$type)
    { 
        return $query->where('type', $type)->with('state')->orderBy('created_at','desc');
    } 

    public function getContactPhoneAttribute($value)
    {

        return $value ? phone_number_format($value) : '';
    }
    public function getPhoneNumberAttribute($value)
    {

        return $value ? phone_number_format($value) : '';
    }
}
