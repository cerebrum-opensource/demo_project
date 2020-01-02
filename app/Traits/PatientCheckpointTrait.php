<?php

namespace App\Traits;

use App\Models\CareplanCheckpoint;
use App\Models\Intervention;
use App\Models\User;
use App\Models\CareplanAssessmentTeam;
use App\Models\Admin\CarePlan\ContentDiscussed;
use App\Models\ManageableField;
use DB;

trait PatientCheckpointTrait {

   
    public function handleInterventionTab()
    {
        $tabContent = [];
        $interventionFollowUps = [];
        $careTeam = [];
        $intervention = new Intervention();
        $flags = ManageableField::where('type','flag')->pluck('value','id');
        if(request()->has('checkpoint_id') && request()->get('checkpoint_id')) {
            $id = encrypt_decrypt('decrypt', request()->get('checkpoint_id'));
            $careplanId = encrypt_decrypt('decrypt', request()->get('careplan_id'));
            $interventionFollowUps = $this->interventionService->getActiveFollowUpList($id,1);
            $careTeam = CareplanAssessmentTeam::where('careplan_id', $careplanId)->first();
            if(request()->has('intervention_id') && request()->get('intervention_id')){
                $interventionId = encrypt_decrypt('decrypt', request()->get('intervention_id'));
                $intervention = Intervention::findOrFail($interventionId);
            }
            
            
        }
        $type = 1;
        if(request()->has('is_view') && request()->get('is_view')) {

            $careTeam = User::where('id', $intervention->assigned_users)->pluck('name','id');
            $tabContent['html'] = view('patients.caseload.checkpoint.view.intervention.index')->with('intervention',$intervention)->with('interventionFollowUps',$interventionFollowUps)->with('careTeam',$careTeam)->with('type',$type)->with('flags',$flags)->render();
        }
        else{
            $tabContent['html'] = view('patients.caseload.checkpoint.intervention.index')->with('intervention',$intervention)->with('interventionFollowUps',$interventionFollowUps)->with('careTeam',$careTeam)->with('type',$type)->with('flags',$flags)->render();
        }
        
        return $tabContent;
    }

   

    public function handleContentDiscussedTab()
    {   
        $content_discussed = [];
        $assessment = new CareplanCheckpoint();
        if(request()->has('checkpoint_id') && request()->get('checkpoint_id')) {
            $assessmentId = encrypt_decrypt('decrypt', request()->get('checkpoint_id'));
            $assessment = CareplanCheckpoint::findOrFail($assessmentId);

            if ($assessment->visit_content) {
                $content_discussed = ContentDiscussed::select(
                                    DB::raw("CONCAT(code,' | ',title) AS code_name"),'id')
                                   ->whereIn('id', $assessment->visit_content)->pluck('code_name','id');
            }
        }
        $tabContent = [];
        if(request()->has('is_view') && request()->get('is_view')) {
            $tabContent['html'] = view('patients.caseload.checkpoint.view.content_discussed')->with('assessment',$assessment)->with('content_discussed',$content_discussed)->render();
        }
        else{
            $tabContent['html'] = view('patients.caseload.checkpoint.content_discussed')->with('assessment',$assessment)->with('content_discussed',$content_discussed)->render();
        }
        
        return $tabContent;
    }


    public function handlePurposeTab()
    { 

        $assessment = new CareplanCheckpoint();
        if(request()->has('checkpoint_id') && request()->get('checkpoint_id')) {
            $assessmentId = encrypt_decrypt('decrypt', request()->get('checkpoint_id'));
            $assessment = CareplanCheckpoint::findOrFail($assessmentId);
        }
        $tabContent = [];
        if(request()->has('is_view') && request()->get('is_view')) {
            $tabContent['html'] = view('patients.caseload.checkpoint.view.purpose')->with('assessment',$assessment)->render();
        }
        else{
            $tabContent['html'] = view('patients.caseload.checkpoint.purpose')->with('assessment',$assessment)->render();
        }
        
        
        return $tabContent;
    }

}