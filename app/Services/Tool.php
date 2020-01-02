<?php

namespace App\Services;

use App\Models\CareplanCheckpoint;
use App\Models\Admin\CarePlan\Tool as ToolModel;

class Tool
{

	public function getToolsList($request)
    {   

        $tools = ToolModel::where('status', ToolModel::ACTIVE);
        if($request->get('name')){
           $tools =   $tools->where(function($q) use($request){ 
                                                $q->orWhere('code', 'like', $request->get('name') . '%')
                                                  ->orWhere('description', 'like', '%'.$request->get('name').'%');
                                            }); 

        }
        $tools =  $tools->orderBy('created_at','desc')->paginate(CASELOAD_PAGINATION_COUNT); 
       
        return  $tools;
    }

    
}
