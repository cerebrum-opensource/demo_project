<?php

namespace App\Services;

use App\Http\Requests\PatientMedication as PatientMedicationRequest;
use App\Models\{CareplanItemHistory, PatientMedication, User, Patient};
use App\Traits\CareplanItemHistoryTrait;
use Illuminate\Contracts\Encryption\DecryptException;
use Auth;
use DB;

class Medication
{
    use CareplanItemHistoryTrait;

    protected $ignoreHistoryItems = [
        'user_type'
    ];

    /**
     * Create a new Medication instance.
     *
     * @return void
     */
	 public function __construct()
	 {
	 
	 }

    public function addMedication($request)
    {
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
        $medicationData = $request->except(['_token', 'medication_id', 'action']);
        
        if($request->has('start_date') && $request->get('start_date')){
            $medicationData['start_date'] = change_date_format($request->get('start_date'));
            $medicationData['status'] = PatientMedication::ACTIVE;
        }

        if($request->has('end_date') && $request->get('end_date')){            
            $medicationData['end_date'] = change_date_format($request->get('end_date'));
            $medicationData['status'] = PatientMedication::INACTIVE;
        }       

        if($request->action == 'add') {
            $save_medication = PatientMedication::create($medicationData);
            $this->addToHistory($save_medication, CareplanItemHistory::TYPE_MEDICATION,'created');

        }else {
            $save_medication = PatientMedication::find($request->medication_id);
            $save_medication = $save_medication->fill($medicationData);
            $this->addToHistory($save_medication, CareplanItemHistory::TYPE_MEDICATION,'updated');
            $save_medication = tap($save_medication)->save();

        }

        if($save_medication)
        {
            $patient = Patient::find($patient_id);
            $patient->calc($patient->random_key);
            $patient->medication_not_required = 0;
            $patient->save();
            DB::commit();
            return true;
        }

        DB::rollBack();
        return false;

    }

    /**
     * Execute the medicationList.
     *
     * @return array
     */
    public function medicationList($patient_id,$isAjax = false)
    {
        if(request()->has('active_records') && request()->get('active_records') == 1) {
            return PatientMedication::where('patient_id', $patient_id)->where('status',PatientMedication::ACTIVE)->orderBy('created_at','desc')->paginate(CASELOAD_PAGINATION_COUNT);
        }

        return PatientMedication::where('patient_id', $patient_id)->orderBy('created_at','desc')->paginate(CASELOAD_PAGINATION_COUNT);
    }


    /**
     * Execute the changeStatus.
     *
     * @return array
     */
    public function changeStatus($request)
    {
        try {
            $id = encrypt_decrypt('decrypt', $request->get('id'));
        }catch(\Exception $e) {
            abort(500);
        }

        $patientMedication = PatientMedication::find($id);
        $patientMedication->status = (int) !$patientMedication->status;
        $patientMedication->reason = $request->get('reason');

        if($patientMedication->status == 0) {
            $patientMedication->end_date = today()->format('Y-m-d');
        } else {
            $patientMedication->start_date = today()->format('Y-m-d');
            $patientMedication->end_date = null;
        }

        $this->addToHistory($patientMedication, CareplanItemHistory::TYPE_MEDICATION,'status');

        $patientMedication->save();
        return $patientMedication;
    }

    /**
     * Execute the patientLists.
     *
     * @return array
     */
    public function medicationDetail($id)
    {
        $data['detail'] = PatientMedication::findOrfail($id);
        $data['history'] = CareplanItemHistory::where([
            'type' => CareplanItemHistory::TYPE_MEDICATION,
            'type_id' => $id
        ])->latest()->paginate(CASELOAD_PAGINATION_COUNT);

        return $data;
    }


}
