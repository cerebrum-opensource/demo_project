<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\DateFormat;
use Illuminate\Database\Eloquent\SoftDeletes;

class PcpInformation extends Model
{
    //
    use DateFormat;
    use SoftDeletes;
    public $timestamps = true;

    protected $dates = ['deleted_at'];

    protected $guarded = ['id','created_at','deleted_at','updated_at'];


    protected $dates_need_to_be_changed = [
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

    public function scopePcpInformation($query)
    { 
        return $query->where('type', 'pcp')->with('state')->orderBy('created_at','desc');
    } 
    public function scopeSpeciality($query)
    { 
        return $query->where('type', 'specialist')->with('state')->orderBy('created_at','desc');
    } 

    public function getContactPhoneAttribute($value)
    {

        return $value ? phone_number_format($value) : '';
    }
    public function getPhoneAttribute($value)
    {

        return $value ? phone_number_format($value) : '';
    }
}
