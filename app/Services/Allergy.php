<?php

namespace App\Services;
use App\Traits\CareplanItemHistoryTrait;
use Auth;
use DB;
use App\Models\{CareplanItemHistory, PatientAllergy, Patient, User};

class Allergy
{

    use CareplanItemHistoryTrait;

    protected $ignoreHistoryItems = [
        'user_type'
    ];

    /**
     * Create a new Allergy instance.
     *
     * @return void
     */
	 public function __construct()
	 {
	 
	 }

	 public function addAllergy($request,$isAssessment=false){

	 	try{
            $patient_id = encrypt_decrypt('decrypt',$request->patient_id);
        } catch (\Exception $e) {
            abort(404);
            exit;
        }

        DB::beginTransaction();

        $request->request->add(['type_id' => Auth::user()->id]);
        $roleName = Auth::user()->roles->pluck('name')[0] ? Auth::user()->roles->pluck('name')[0] : MANAGERDIRECTOR;
        $request->request->add(['user_type' => strtolower($roleName)]);
        $request->request->add(['patient_id' => $patient_id]);             
        $allergyData = $request->except(['_token', 'allergy_id', 'action']);


        if($request->has('start_date') && $request->get('start_date')){
            $allergyData['start_date'] = change_date_format($request->get('start_date'));            
            $allergyData['status'] = PatientAllergy::ACTIVE;
        }

        if($request->has('end_date') && $request->get('end_date')){
            $allergyData['end_date'] = change_date_format($request->get('end_date'));
            $allergyData['status'] = PatientAllergy::INACTIVE;
        }          

        if($request->action == 'add') {            
            $save_allergy = PatientAllergy::create($allergyData);
            $this->addToHistory($save_allergy, CareplanItemHistory::TYPE_ALLERGY,'created');
        }
        else {
            $save_allergy = PatientAllergy::find($request->allergy_id);
            $save_allergy = $save_allergy->fill($allergyData);
            $this->addToHistory($save_allergy, CareplanItemHistory::TYPE_ALLERGY,'updated');
            $save_allergy = $save_allergy->save();
        }
        

        if($save_allergy)
        {
        	if($isAssessment){
        		$patient = Patient::find($patient_id);
	            $patient->calc($patient->random_key);
	            $patient->allergy_not_required = 0;
	            $patient->save();
        	}
            DB::commit();
            return 1;
        }
        DB::rollBack();
        return 0;

	 }

	  /**
     * Execute the patientLists.
     *
     * @return array
     */
    public function allergyLists($patient_id,$isAjax = false)
    {
    	$previous_allergies = PatientAllergy::where('patient_id', $patient_id)->orderBy('created_at','desc')->paginate(CASELOAD_PAGINATION_COUNT);
        return  $previous_allergies;

    }

    /**
     * Execute the patientLists.
     *
     * @return array
     */
    public function allergyDetail($id)
    {
        $data['detail'] = PatientAllergy::findOrfail($id);
        $data['history'] = CareplanItemHistory::where([
            'type' => CareplanItemHistory::TYPE_ALLERGY,
            'type_id' => $id
        ])->latest()->paginate(CASELOAD_PAGINATION_COUNT);

        return $data;
    }

    public function changeStatus($request)
    {
        try {
            $id = encrypt_decrypt('decrypt', $request->get('id'));
        }catch(\Exception $e) {
            abort(500);
        }

        $allergy = PatientAllergy::find($id);
        $allergy->status = (int) !$allergy->status;
        $allergy->reason = $request->get('reason');

        if($allergy->status == 0) {
            $allergy->end_date = today()->format('Y-m-d');
        } else {
            $allergy->start_date = today()->format('Y-m-d');
            $allergy->end_date = null;
        }

        $this->addToHistory($allergy, CareplanItemHistory::TYPE_ALLERGY,'status');
        $allergy->save();

        return $allergy;
    }

}
