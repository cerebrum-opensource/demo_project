<?php

namespace App\Models\Admin\CarePlan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tool extends Model
{
    //
	use SoftDeletes;
	
    public $timestamps = true;

    const ACTIVE = '1';
    const DRAFT = '0';
    const INACTIVE = '2';
    const TOOL_PREFIX = 'TID';

    const TOOL_TYPE_PDF = 'Pdf';
    const TOOL_TYPE_ONLINE = 'Online';

    protected $dates = ['deleted_at'];

    protected $guarded = ['id','created_at','deleted_at','updated_at'];

    public function scopeActive($query)
    { 
        return $query->whereNotIn('status', [self::INACTIVE])->orderBy('created_at','desc');
    }

    /**
     * check tool is assigned or not
     *
     * @return count
    */

    public function hasGoal()
    {
        return GoalAssignment::where('type', 'tool')->where('type_id', $this->id)->count();
    }

}
