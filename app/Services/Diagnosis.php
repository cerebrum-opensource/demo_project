<?php

namespace App\Services;

use App\Models\Admin\CarePlan\Diagnosis as DiagnosisModel;
use App\Models\Admin\CarePlan\GoalAssignment;
use App\Models\CareplanDiagnosis;

Class Diagnosis
{
    /**
     * Add Diagnosis
     */
    public function add()
    {

    }

    /**
     * Edit Diagnosis
     */
    public function edit()
    {

    }

    /**
     * Update Diagnosis
     */
    public function update()
    {

    }

    /**
     * Delete Diagnosis
     */
    public function delete()
    {

    }

    /**
     * Get diagnosis details
     */
    public function getDiagnosisDetails()
    {

    }

    /**
     * Get diagnosis lists
     */
    public function getDiagnosisListForDropDown($request)
    {   
        $diagnosis = [];
        if ($request->has('data') && $request->input('data') !='') {

            $diagnosisIds = [];
            if($request->has('careplanId') && $request->get('careplanId')) {
                $careplanId = encrypt_decrypt('decrypt', $request->get('careplanId'));
                $diagnosisIds = CareplanDiagnosis::where('careplan_id', $careplanId)->get()->pluck('diagnosis_id');
            }


            $diagnosis = DiagnosisModel::select('diagnosis.*','ga.type_id')
                ->where(function($q) use($request) {
                    $q->orWhere('code', 'like', $request->input('data') . '%')
                      ->orWhere('title', 'like', '%'.$request->input('data').'%');
                })
                ->join('goal_assignments as ga', function($join){
                    $join->on('ga.type_id', '=', 'diagnosis.id');
                    $join->on('ga.type_version', '=', 'diagnosis.current_version');
                });

                if($diagnosisIds) {
                    $diagnosis->whereNotIn('diagnosis.id', $diagnosisIds);
                }

            $diagnosis = $diagnosis->where('ga.type','diagnosis')
                ->where('ga.status',GoalAssignment::ACTIVE)->groupBy('ga.type_id')->limit(50)->get();
        }

             return $diagnosis;

    }


}