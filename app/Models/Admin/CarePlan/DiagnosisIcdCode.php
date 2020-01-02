<?php

namespace App\Models\Admin\CarePlan;

use App\Models\IcdCode;
use Illuminate\Database\Eloquent\Model;

class DiagnosisIcdCode extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'diagnosis_id', 'icd_code_id', 'version'
    ];
    
    /**
     * Relationship: diagnosis
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
    */
    
    public function diagnosis()
    {
        return $this->belongsTo('App\Models\Admin\CarePlan\Diagnosis');
    }


    public function icdCodes()
    {
        return $this->hasOne(IcdCode::class, 'id', 'icd_code_id');
    }
}
