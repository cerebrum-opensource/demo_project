<?php

namespace App\Models\Admin\CarePlan;

use App\Models\CareplanGoal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Goal extends Model
{
	use SoftDeletes;
	
	protected $table = 'goals';
	
    const ACTIVE = '1';
    const DRAFT = '0';
    const INACTIVE = '2';
    const PARTIALLY_SAVE = '3';
    const GOAL_PREFIX = 'GID';
    const DEFAULT_VERSION = '1.0';


    const ACTIVE_NAME = 'Active';
    const DRAFT_NAME = 'Draft';
    const INACTIVE_NAME = 'Deactive';
    const FLAG_YES = '1';
    const FLAG_NO = '0';
    const TYPE_QUALITATIVE = '0';
    const TYPE_QUANTATIVE = '1';


    /*  Goal Data Type*/

    const SUB_GOALS = 'subgoals';
    const QUESTIONS = 'questions';
    const BARRIERS = 'barriers';
    const TOOLS = 'tools';
    const DIAGNOSIS = 'diagnosis';
    
    protected $dates = ['deleted_at'];

    protected $guarded = ['id','created_at','deleted_at','updated_at'];
    
    public function scopeActive($query)
    { 
        return $query->whereNotIn('goals.status', [self::PARTIALLY_SAVE])->orderBy('goals.created_at','desc');
    }
     

    /**
     * Relationship: sub_goals
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
    */

    public function sub_goals() {
        return $this->belongsToMany('App\Models\Admin\CarePlan\SubGoal', 'goal_assignments', 'goal_id', 'type_id')
            ->where('type','sub_goal')
            ->where('goal_assignments.version',$this->current_version)
            ->whereNull('goal_assignments.deleted_at')
            ->orderBy('sub_goals.updated_at', 'desc');
    }

    /**
     * Relationship: questions
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
    */
    public function questions() {
        return $this->belongsToMany('App\Models\Admin\CarePlan\Question', 'goal_assignments', 'goal_id', 'type_id')
            ->where('type','question')
            ->where('goal_assignments.version',$this->current_version)
            ->whereNull('goal_assignments.deleted_at')
            ->orderBy('questions.updated_at', 'desc');
    }

    /**
     * Relationship: barriers
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
    */
    public function barriers() {
        return $this->belongsToMany('App\Models\Admin\CarePlan\Barrier', 'goal_assignments', 'goal_id', 'type_id')
            ->where('type','barrier')
            ->where('goal_assignments.version',$this->current_version)
            ->whereNull('goal_assignments.deleted_at')
            ->orderBy('barriers.updated_at', 'desc');
    }

    /**
     * Relationship: tools
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
    */
    public function tools() {
        return $this->belongsToMany('App\Models\Admin\CarePlan\Tool', 'goal_assignments', 'goal_id', 'type_id')->where('goal_assignments.type','tool')
            ->where('goal_assignments.version',$this->current_version)
            ->whereNull('goal_assignments.deleted_at');
    }

    /**
     * Relationship: diagnosis
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
    */
    public function diagnosis() {
        return $this->belongsToMany('App\Models\Admin\CarePlan\Diagnosis', 'goal_assignments', 'goal_id', 'type_id')->where('type','diagnosis')
            ->where('goal_assignments.version',$this->current_version)
            ->whereNull('goal_assignments.deleted_at');
    }
    

    /**
     * Relationship: diagnosis
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
    */
    public function latest_diagnosis() {
        return $this->belongsToMany('App\Models\Admin\CarePlan\Diagnosis', 'goal_assignments', 'goal_id', 'type_id')->where('type','diagnosis')->whereNull('goal_assignments.deleted_at');
    }

    /**
     * Function to return status value
     *
     * @return text
    */

    public function getStatusNameAttribute()
    {
        $status='';
        switch ($this->attributes['status']) {
        case Goal::DRAFT:
            $status = Goal::DRAFT_NAME;
            break;
        case Goal::ACTIVE:
            $status = Goal::ACTIVE_NAME;
            break;
        case Goal::INACTIVE:
            $status = Goal::INACTIVE_NAME;
            break;
        default:
            $status = Goal::DRAFT_NAME;
            break;
        }
        return $status;
    }

    /**
     * Relationship: versions
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
    */
    public function versions()
    {
        return $this->hasMany('App\Models\Admin\CarePlan\Goal');
    }

    public function hasCareplanGoal()
    {
        return CareplanGoal::where('goal_id', $this->id)->count();
    }


   
}
