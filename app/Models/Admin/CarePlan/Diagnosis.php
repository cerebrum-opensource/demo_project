<?php

namespace App\Models\Admin\CarePlan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Diagnosis extends Model
{
	use SoftDeletes;
	
	protected $table = 'diagnosis';
	
    const ACTIVE = '1';
    const DRAFT = '0';
    const INACTIVE = '2';
    const DIAGNOSIS_PREFIX = 'DID';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code', 'title', 'description', 'metric_id', 'status','current_version'
    ];
    
    public function scopeActive($query)
    { 
        return $query->whereNotIn('status', [self::INACTIVE])->orderBy('created_at','desc');
    }
    
    /**
     * Relationship: icd_codes
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
    */

    public function icd_codes()
    {
        return $this->hasMany('App\Models\Admin\CarePlan\DiagnosisIcdCode');
    }

    /**
     * Relationship: versions
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
    */
    
    public function versions()
    {
        return $this->hasMany('App\Models\Admin\CarePlan\DiagnosisVersion');
    }


    /**
     * check diagnosis is assigned or not
     *
     * @return count
    */

    public function hasGoal()
    {
        return GoalAssignment::where('type', 'diagnosis')->where('type_id', $this->id)->count();
    }
}
