<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

class ReferralSource extends Model
{
    //
    use Cachable;
	use SoftDeletes;
	
    public $timestamps = true;

    protected $dates = ['deleted_at'];

    protected $guarded = ['id','created_at','deleted_at','updated_at'];

    public function getContactPhoneAttribute($value)
    {

        return $value ? phone_number_format($value) : '';
    }
    public function getPhoneAttribute($value)
    {

        return $value ? phone_number_format($value) : '';
    }
}
