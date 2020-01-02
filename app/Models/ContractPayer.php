<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\DateFormat;
use Illuminate\Database\Eloquent\SoftDeletes;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

class ContractPayer extends Model
{
	use Cachable;
    use DateFormat;
	use SoftDeletes;

	protected $guarded = ['id','created_at','deleted_at','updated_at'];	
 	protected $dates = ['deleted_at'];

	protected $dates_need_to_be_changed = [
        'start_date',
        'end_date',
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

    public function getContactPhoneAttribute($value)
    {

        return $value ? phone_number_format($value) : '';
    }
    public function getPhoneNumberAttribute($value)
    {

        return $value ? phone_number_format($value) : '';
    }
}
