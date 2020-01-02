<?php

namespace App\Models\Admin\CarePlan;

use App\Models\CareplanAssessmentGoalReviewData;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
	use SoftDeletes;
	
	protected $table = 'questions';
	
    const ACTIVE = '1';
    const DRAFT = '0';
    const INACTIVE = '2';

    const GOAL_TYPE = '0';
    const PRIORITY_ALIGNMENT = '1';
    const RISK_ASSESSMENT = '2';
 
    
    protected $dates = ['deleted_at'];

    protected $guarded = ['id','created_at','deleted_at','updated_at'];
    
    protected $casts = [
        'assigned_roles' => 'array'
    ];
    

    public function getRoles()
    {
        $roles = Role::whereIn('id', $this->assigned_roles)->pluck('name')->toArray();
        if($roles) {
            return implode($roles,',');
        }

        return '';
    }


    /**
     * Relationship: metric
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasOne
    */

    public function metric()
    {
        return $this->hasOne('App\Models\ManageableField','id', 'metric_id');
    }

    public function goalReviewData()
    {
        return $this->hasOne(CareplanAssessmentGoalReviewData::class,'question_id', 'id');
    }

    public function getAnswer()
    {
        if($this->goalReviewData) {
           return $this->goalReviewData->answer;
        }

        return '';
    }

    public function getVisits()
    {
        if($this->goalReviewData && $this->goalReviewData->no_of_visits) {
            return $this->goalReviewData->no_of_visits;
        }

        return '';
    }

}
