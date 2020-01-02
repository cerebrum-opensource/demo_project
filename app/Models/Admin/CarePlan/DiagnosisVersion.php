<?php

namespace App\Models\Admin\CarePlan;

use Illuminate\Database\Eloquent\Model;

class DiagnosisVersion extends Model
{

    const ACTIVE = '1';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code', 'title', 'description', 'metric_id', 'diagnosis_id', 'status', 'version'
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

    public function icd_codes()
    {
        return $this->hasMany('App\Models\Admin\CarePlan\DiagnosisIcdCode','diagnosis_id','diagnosis_id');
    }
}
