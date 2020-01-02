<?php

namespace App\Models\Admin\CarePlan;

use App\Models\Admin\CarePlan\Goal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoalAssignment extends Model
{
	use SoftDeletes;
	
	protected $table = 'goal_assignments';

	const ACTIVE = '1';
    const PARTIALLY_SAVE = '0';
    const PARTIALLY_DELETED = '2';
   
    protected $dates = ['deleted_at'];

    protected $guarded = ['id','created_at','deleted_at','updated_at'];


    public function handleGoalTypeDelete()
    {
        if($this->status == GoalAssignment::ACTIVE) {
            $this->status = GoalAssignment::PARTIALLY_DELETED;
            $this->save();
        } else{
            $this->delete();
        }
    }

    public function goal()
    {
        return $this->hasOne(Goal::class,'id','goal_id');
    }

    public function subgoal()
    {
        return $this->hasOne(SubGoal::class,'id','type_id');
    }

    public function question()
    {
        return $this->hasOne(Question::class,'id','type_id');
    }


    
    
}
