<?php

namespace App\Models\Admin\CarePlan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role;

class SubGoal extends Model
{
	use SoftDeletes;
	
	protected $table = 'sub_goals';
	
    const ACTIVE = '1';
    const DRAFT = '0';
    const INACTIVE = '2';
 
    
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
    
}
