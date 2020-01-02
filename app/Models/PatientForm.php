<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

class PatientForm extends Model
{
    //
    use Cachable;
    protected $guarded = ['id','created_at','deleted_at','updated_at'];



    public function authorize()
    {
        return $this->belongsTo('App\Models\User','authorize_by');
    }
}
