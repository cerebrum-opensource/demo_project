<?php

namespace App\Models\Admin\CarePlan;

use App\Models\AssessmentBarrier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Barrier extends Model
{
	use SoftDeletes;
	
	protected $table = 'barriers';
	
    const ACTIVE = '1';
    const DRAFT = '0';
    const INACTIVE = '2';
    
    const BARRIER_PREFIX = 'BID';

    protected $dates = ['deleted_at'];

    protected $guarded = ['id','created_at','deleted_at','updated_at'];


    public function scopeActive($query)
    { 
        return $query->whereNotIn('status', [self::INACTIVE])->orderBy('created_at','desc');
    }
    
    
    /**
     * check barrier is assigned or not
     *
     * @return count
    */

    public function hasGoal()
    {
        return GoalAssignment::where('type', 'barrier')->where('type_id', $this->id)->count();
    }

    public function category()
    {
        return $this->hasOne('App\Models\ManageableField','id', 'category_id');
    }

    public function isAssessmentBarrier()
    {
        return AssessmentBarrier::where('barrier_id',$this->id)->count();
    }
}
