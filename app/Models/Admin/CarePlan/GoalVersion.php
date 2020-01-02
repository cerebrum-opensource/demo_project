<?php

namespace App\Models\Admin\CarePlan;

use App\Models\ManageableField;
use Illuminate\Database\Eloquent\Model;

class GoalVersion extends Model
{

	protected $table = 'goal_versions';
    protected $dates = ['deleted_at'];
    protected $guarded = ['id','created_at','deleted_at','updated_at'];

    const IS_DRAFT_NO = '0';
    const IS_DRAFT_YES = '1';

    const ACTIVE = '1';


    /**
     * Relationship: goals
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
    */ 	
   
    public function goals()
    {
        return $this->belongsTo('App\Models\Admin\CarePlan\Goal');
    }

    public function getSubgoals()
    {
        return GoalAssignment::where([
            'goal_id' => $this->goal_id,
            'version' => $this->version,
            'type' => 'sub_goal',
            'status' => GoalAssignment::ACTIVE
        ])->with('subgoal');
    }

    public function getQuestions()
    {
        return GoalAssignment::where([
            'goal_id' => $this->goal_id,
            'version' => $this->version,
            'type' => 'question',
            'status' => GoalAssignment::ACTIVE
        ])->with('question.metric');
    }

    public function getTypeText()
    {
        if($this->type == Goal::TYPE_QUALITATIVE)
        {
            return 'Qualitative';
        }

        return 'Quantitative';
    }

    public function getFlagText()
    {
        if($this->flag == Goal::FLAG_YES)
        {
            return 'Yes';
        }

        return 'No';
    }

    public function getMatrixText()
    {
        $managableData = ManageableField::where('type','metric')->where('id',$this->metric_id)->first();
        if ($managableData) {
            return $managableData->name;
        }

        return '';
    }

}
