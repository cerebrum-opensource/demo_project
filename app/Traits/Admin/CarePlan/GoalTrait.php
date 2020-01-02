<?php

namespace App\Traits\Admin\CarePlan;
use App\Models\Admin\CarePlan\{ SubGoal, Goal, GoalAssignment, Question, Barrier, GoalVersion, Diagnosis };


use Illuminate\Support\Facades\DB;

trait GoalTrait
{
	//Function to add goal in database
    public function saveGoal($request)
    {
		
        DB::beginTransaction();
        if($request->goal_id){
            $id = encrypt_decrypt('decrypt', $request->goal_id);
            $response = Goal::find($id); 

            $current_version = $response->current_version;
            $current_status = $response->status;

            if($response->status == Goal::ACTIVE) {
                $request->request->add(['current_version' => DB::raw('current_version + 0.1')]);
            }

            $data= $request->except('_token','id','goal_id','is_added');
            $is_updated = $response->update($data);

            if($response->current_version == Goal::DEFAULT_VERSION) {
                GoalAssignment::where('goal_id',$id)->where('status',GoalAssignment::PARTIALLY_SAVE)->update(['status'=>GoalAssignment::ACTIVE]);
                GoalAssignment::where('goal_id',$id)->where('status',GoalAssignment::PARTIALLY_DELETED)->delete();
                $request->session()->flash('message.level','success');
                if($request->status == 0){
                    $request->session()->flash('message.content',trans('message.goal_draft_successfully'));
                }   
                else {
                    $request->session()->flash('message.content',trans('message.goal_added_successfully'));
                }

            }


            if($is_updated){
                $goal = Goal::where('id',$id)->first();
                  if($current_status == Goal::ACTIVE && ($request->status == Goal::ACTIVE || $request->status == Goal::DRAFT))
                     $this->saveVersion($request->except(['current_version','is_added']),$goal,$current_version,$request);
                  if($current_status ==  Goal::DRAFT && $request->status ==  Goal::ACTIVE)
                     $this->updateDraftVersion($request->except(['current_version','is_added','_token','id',]),$goal,$current_version,$request);
                  if($current_status ==  Goal::DRAFT && $request->status ==  Goal::DRAFT)
                     $this->updateDraftGoal($request->except(['current_version','is_added']),$goal,$current_version,$request);
                  if($current_status ==  Goal::PARTIALLY_SAVE && $request->status ==  Goal::ACTIVE)
                     $this->updateDraftVersion($request->except(['current_version','is_added']),$goal,$current_version,$request);

            } else {
                DB::rollBack();
                return false;
            }

            $goal_id = $response->id;
        }
        else {
            $data = $request->except('_token','id','goal_id','is_added');
           // $data['status'] = Goal::PARTIALLY_SAVE;
            $data['current_version'] = Goal::DEFAULT_VERSION;
            $response = Goal::create($data);  
            if($response){
                $goal_id = $response->id;
    			$tool_code = $this->generateGoalID($response->id);
    			$is_updated = $response->update(['code' => $tool_code]);

                if($response->status == 1)
                    $this->saveVersion($request->except(['is_added']),$response,Goal::DEFAULT_VERSION);
                if(!$is_updated){
                    DB::rollBack();
                    return false;
                }  
    		}
            else
            {
                DB::rollBack();
                return false;
            }
        }
        DB::commit();
        return $goal_id;
    }
    

    //Function to generate Diagnosis ID
    public function generateGoalID($id)
    {
		return Goal::GOAL_PREFIX.$id;
    }


    //Function to add/update sub goal 
    public function saveOrUpdateSubGoal($request)
    {
        $subgoalId = 0;
        if ($request->get('id')) {
            $subgoalId = $request->get('id');
        }

        DB::beginTransaction();
        $id = encrypt_decrypt('decrypt', $request->goal_id);
        $request->request->add(['goal_id' => $id]);
        $data = $request->except('_token','save_type','goal_id','id');
        $data['status'] = SubGoal::ACTIVE;

        if ($subgoalId) {
            $goal = Goal::find($id);
            $subGoal = GoalAssignment::where('type', 'sub_goal')->where('type_id', $subgoalId)->where('version', $goal->current_version)->first();

            $subGoalData = SubGoal::find($subgoalId);
            $data['created_at'] = $subGoalData->created_at;
            $data['updated_at'] = $subGoalData->updated_at;

            if($subGoal->status == GoalAssignment::ACTIVE) {
                $subGoal->status = GoalAssignment::PARTIALLY_DELETED;
                $subGoal->save();
            }
            if($subGoal->status == GoalAssignment::PARTIALLY_SAVE) {
                $subGoal->forceDelete();
            }
        }

        $response = SubGoal::create($data);


        if($response) {
            $subgoalId ?  SubGoal::where('id',$response->id)->update(['created_at'=>$data['created_at'],'updated_at'=>$data['updated_at']]) : '';

            $goal = Goal::find($id);
            $sub_goal_data['type_id'] = $response->id;
            $sub_goal_data['type'] = 'sub_goal';
            $sub_goal_data['goal_id'] = $id;
            $sub_goal_data['version'] = $goal->current_version;
            $response = GoalAssignment::create($sub_goal_data);
            if(!$response){
                $request->session()->flash('message.SubGoal-level','danger');
                $request->session()->flash('message.content',trans('message.error_added_sub_goal'));
                DB::rollBack();
                return false;
            }
        }
        else
        {   
            $request->session()->flash('message.SubGoal-level','danger');
            $request->session()->flash('message.content',trans('message.error_added_sub_goal'));
            DB::rollBack();
            return false;
        }

        $request->session()->flash('message.SubGoal-level','success');
        $subgoalId ? $request->session()->flash('message.content',trans('message.sub_goal_updated_successfully')) : $request->session()->flash('message.content',trans('message.sub_goal_added_successfully'));
        DB::commit();
        return $id;
    }

    //Function to add/update question 
    public function saveOrUpdateQuestion($request)
    {
       $questionId = 0;
        if ($request->get('id')) {
            $questionId = $request->get('id');
        }

        DB::beginTransaction();
        $id = encrypt_decrypt('decrypt', $request->goal_id);
        $request->request->add(['goal_id' => $id]);
        $data = $request->except('_token','save_type','goal_id');
        $data['status'] = Question::ACTIVE;

        if ($questionId) {

            $goal = Goal::find($id); 
            $question = GoalAssignment::where('type', 'question')->where('type_id',encrypt_decrypt('decrypt', $questionId))->where('version', $goal->current_version)->first();

            $questionData = Question::find(encrypt_decrypt('decrypt', $questionId));
            $data['created_at'] = $questionData->created_at;
            $data['updated_at'] = $questionData->updated_at;
            if($question->status == GoalAssignment::ACTIVE){
                $question->status = GoalAssignment::PARTIALLY_DELETED;
                $question->save();
            }

            if($question->status == GoalAssignment::PARTIALLY_SAVE){
                $question->forceDelete();
            }
        }

        $response = Question::create($data);
        if($response){
            $questionId ?  Question::where('id',$response->id)->update(['created_at'=>$data['created_at'],'updated_at'=>$data['updated_at']]) : '';
            $goal = Goal::find($id);  
            $sub_goal_data['type_id'] = $response->id;
            $sub_goal_data['type'] = 'question';
            $sub_goal_data['goal_id'] = $id;
            $sub_goal_data['version'] = $goal->current_version;
            $response = GoalAssignment::create($sub_goal_data);
            if(!$response){
                $request->session()->flash('message.Questions-level','danger');
                $request->session()->flash('message.content',trans('message.error_added_question'));
                DB::rollBack();
                return false;
            }  
        }
        else
        {
            $request->session()->flash('message.Questions-level','danger');
            $request->session()->flash('message.content',trans('message.error_added_question'));
            DB::rollBack();
            return false;
        }
        $request->session()->flash('message.Questions-level','success');
        $questionId ? $request->session()->flash('message.content',trans('message.question_updated_successfully')) : $request->session()->flash('message.content',trans('message.question_added_successfully'));
        DB::commit();
        return $id;
    }

    //Function to add/update barrier 
   /* public function saveOrUpdateBarrier($request)
    {
        $barrierId = 0;
        if ($request->get('id')) {
            $barrierId = $request->get('id');
        }

        DB::beginTransaction();
        $id = encrypt_decrypt('decrypt', $request->goal_id);
        $request->request->add(['goal_id' => $id]);
        $data = $request->except('_token','save_type','goal_id');
        $data['status'] = Barrier::ACTIVE;
        if ($barrierId) {
            $goal = Goal::find($id); 
            $barrier = GoalAssignment::where('type', 'barrier')->where('type_id',encrypt_decrypt('decrypt', $barrierId))->where('version', $goal->current_version)->first();


            $barrierData = Barrier::find(encrypt_decrypt('decrypt', $barrierId));
            $data['created_at'] = $barrierData->created_at;
            $data['updated_at'] = $barrierData->updated_at;
            if($barrier->status == GoalAssignment::ACTIVE){
                $barrier->status = GoalAssignment::PARTIALLY_DELETED;
                $barrier->save();
            }

            if($barrier->status == GoalAssignment::PARTIALLY_SAVE){
                $barrier->forceDelete();
            }
        }
        $response = Barrier::create($data);
        if($response){
            $barrierId ?  Barrier::where('id',$response->id)->update(['created_at'=>$data['created_at'],'updated_at'=>$data['updated_at']]) : '';
            $goal = Goal::find($id);  
            $sub_goal_data['type_id'] = $response->id;
            $sub_goal_data['type'] = 'barrier';
            $sub_goal_data['goal_id'] = $id;
            $sub_goal_data['version'] = $goal->current_version;
            $response = GoalAssignment::create($sub_goal_data);
            if(!$response){
                DB::rollBack();
                $request->session()->flash('message.Barriers-level','danger');
                $request->session()->flash('message.content',trans('message.error_added_barrier'));
                return false;
            }  
        }
        else
        {   

            $request->session()->flash('message.Barriers-level','danger');
            $request->session()->flash('message.content',trans('message.error_added_barrier'));
            DB::rollBack();
            return false;
        }
        $request->session()->flash('message.Barriers-level','success');
        $barrierId ? $request->session()->flash('message.content',trans('message.barrier_updated_successfully')) : $request->session()->flash('message.content',trans('message.barrier_added_successfully'));
        DB::commit();
        return $id;
    }*/


    //Function to check the pagination request 
    public function isPaginateRequest()
    {
        $request = request();
        if ($request->ajax() && $request->has('page')) {
            if ($request->get('page')) {
                return true;
            }
        }

        return false;
    }

    //Function to save Tool corresponding to goal
    public function saveTool($request)
    {
       
        DB::beginTransaction();
        $id = encrypt_decrypt('decrypt', $request->goal_id);
        if(!empty($request->tool_id)){
            $goal = Goal::find($id);  
                foreach ($request->tool_id as $key => $tool) 
                    {
                        $tool_data['type_id'] = $tool;
                        $tool_data['type'] = 'tool';
                        $tool_data['goal_id'] = $id;
                        $tool_data['version'] = $goal->current_version;
                        $response = GoalAssignment::create($tool_data);
                        if($response)
                            {
                                
                            }
                        else {
                            DB::rollBack();
                            return false;
                        } 

                    }
        }
        
        $request->session()->flash('message.Tools-level','success');
        $request->session()->flash('message.content',trans('message.tool_added_successfully'));
        DB::commit();
        return $id;
    }


    //Function to save Barrier corresponding to goal
    public function saveBarrier($request)
    {
       
        DB::beginTransaction();
        $id = encrypt_decrypt('decrypt', $request->goal_id);
        if(!empty($request->barrier_id)){
            $goal = Goal::find($id);  
                foreach ($request->barrier_id as $key => $tool) 
                    {
                        $tool_data['type_id'] = $tool;
                        $tool_data['type'] = 'barrier';
                        $tool_data['goal_id'] = $id;
                        $tool_data['version'] = $goal->current_version;
                        $response = GoalAssignment::create($tool_data);
                        if($response)
                            {
                                
                            }
                        else {
                            DB::rollBack();
                            return false;
                        } 

                    }
        }
        
        $request->session()->flash('message.Barriers-level','success');
        $request->session()->flash('message.content',trans('message.barrier_added_successfully'));
        DB::commit();
        return $id;
    }

    //Function to save Diagnosis corresponding to goal
    public function saveDiagnosis($request)
    {
       
        DB::beginTransaction();
        $id = encrypt_decrypt('decrypt', $request->goal_id);
        if(!empty($request->diagnosis_id)){
            $goal = Goal::find($id);  
                foreach ($request->diagnosis_id as $key => $diagnosis) 
                    {
                        $diagnosisData = Diagnosis::find($diagnosis);
                        $tool_data['type_id'] = $diagnosis;
                        $tool_data['type'] = 'diagnosis';
                        $tool_data['goal_id'] = $id;
                        $tool_data['version'] = $goal->current_version;
                        $tool_data['type_version'] = $diagnosisData->current_version;
                        $response = GoalAssignment::create($tool_data);
                        if($response)
                            {
                                
                            }
                        else {
                            DB::rollBack();
                            return false;
                        } 

                    }
        }

        $request->session()->flash('message.Diagnosis-level','success');
        $request->session()->flash('message.content',trans('message.diagnosis_created_successfully'));
        
        DB::commit();
        return $id;

    }



    //function to save versions of Goal and update its goal assigment with latest version
    public function saveVersion($data,$goal,$version,$request)
    {
        $data['goal_id'] = $goal->id;
        $data['version'] = $goal->current_version;
        $data['code'] = $goal->code;
        $data['is_draft'] = $goal->status == Goal::DRAFT ? GoalVersion::IS_DRAFT_YES : GoalVersion::IS_DRAFT_NO;
        $goal_version = new GoalVersion($data);
        $update = $goal->versions()->save($goal_version);

        if($goal->current_version != Goal::DEFAULT_VERSION){
            $goalAssignmentVersions = GoalAssignment::where('goal_id',$goal->id)->where('version', $version)->get();

            GoalAssignment::where('goal_id',$goal->id)
                ->where('status',GoalAssignment::PARTIALLY_SAVE)
                ->forceDelete();
            GoalAssignment::where('goal_id',$goal->id)
                ->where('status',GoalAssignment::PARTIALLY_DELETED)
                ->update(['status'=>GoalAssignment::ACTIVE]);


            foreach ($goalAssignmentVersions as $goalAssignmentVersion) {
                if($goalAssignmentVersion->status === GoalAssignment::PARTIALLY_DELETED){
                    continue;
                }

                $assignment_data['type_id'] = $goalAssignmentVersion->type_id;
                $assignment_data['type'] = $goalAssignmentVersion->type;
                $assignment_data['goal_id'] = $goalAssignmentVersion->goal_id;
                $assignment_data['version'] = $goal->current_version;
                $assignment_data['status'] = GoalAssignment::ACTIVE;
                $assignment_data['type_version'] = $goalAssignmentVersion->type_version;
                $response = GoalAssignment::create($assignment_data);     
                          
            }
            $request->session()->flash('message.level','success');
            if($request->status == 0){
                $request->session()->flash('message.content',trans('message.goal_draft_successfully'));
            }   
            else {
                $request->session()->flash('message.content',trans('message.goal_added_successfully'));
            }
           
            
        }
        
       // DB::commit();
        return $update;

    }


    public function handleDeleteGoalType($request)
    {
        $type = '';
        if($request->type == 'subgoals') {
            $type = 'sub_goal';
        }

        if($request->type == 'diagnosis') {
            $type = 'diagnosis';
        }

        if($request->type == 'tools') {
            $type = 'tool';
        }

        if($request->type == 'questions') {
            $type = 'question';
        }

        if($request->type == 'barriers') {
            $type = 'barrier';
        }

        if($type) {
            $id = encrypt_decrypt('decrypt',$request->get('id'));
            $goal_id = encrypt_decrypt('decrypt',$request->get('goal_id'));
            $goal = Goal::find($goal_id);
            $goalAssignment = GoalAssignment::where('type', $type)->where('type_id', $id)->where('goal_id', $goal_id)->where('version', $goal->current_version)->first();
            $goalAssignment->handleGoalTypeDelete();
            return response()->json(['message' => $type.' Deleted'], 200);
        }

        return response()->json(['message' => ''], 200);
    }


    //function to update the version of goal from draft to public

     public function updateDraftVersion($data,$goal,$version,$request)
    {
        
       
        if(GoalVersion::where('goal_id',$goal->id)->where('version',$goal->current_version)->count() == 1){
            $update = GoalVersion::where('goal_id',$goal->id)->where('version',$goal->current_version)->update(['is_draft'=>GoalVersion::IS_DRAFT_NO,'status'=>Goal::ACTIVE]);
        }
        else{
            $data['goal_id'] = $goal->id;
            $data['version'] = $goal->current_version;
            $data['code'] = $goal->code;
            $data['is_draft'] = $goal->status == Goal::DRAFT ? GoalVersion::IS_DRAFT_YES : GoalVersion::IS_DRAFT_NO;
            $goal_version = new GoalVersion($data);
            $update = $goal->versions()->save($goal_version); 
        }

        $request->session()->flash('message.level','success');
        if($request->status == 0){
                $request->session()->flash('message.content',trans('message.goal_draft_successfully'));
            }   
            else {
                $request->session()->flash('message.content',trans('message.goal_added_successfully'));
            }
        return $update;
    }



    //function to add goal assignment for draft when first time goal added

     public function updateDraftGoal($data,$goal,$version,$request)
    {
        
        
        if($goal->current_version != Goal::DEFAULT_VERSION){
            $goalAssignmentVersions = GoalAssignment::where('goal_id',$goal->id)->where('version', $version)->get();

            GoalAssignment::where('goal_id',$goal->id)
                ->where('status',GoalAssignment::PARTIALLY_SAVE)
                ->update(['status'=>GoalAssignment::ACTIVE]);
            GoalAssignment::where('goal_id',$goal->id)
                ->where('status',GoalAssignment::PARTIALLY_DELETED)
                ->delete(); 
        }

        $request->session()->flash('message.level','success');
        if($request->status == 0){
                $request->session()->flash('message.content',trans('message.goal_draft_successfully'));
            }   
            else {
                $request->session()->flash('message.content',trans('message.goal_added_successfully'));
            }
        return 1;
    }

}



?>
