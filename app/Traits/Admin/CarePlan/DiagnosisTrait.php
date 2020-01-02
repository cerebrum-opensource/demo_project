<?php

namespace App\Traits\Admin\CarePlan;

use App\Models\Admin\CarePlan\{Diagnosis,DiagnosisIcdCode,DiagnosisVersion};
use DB;

trait DiagnosisTrait
{
	//Function to add diagnosis in database
    public function createDiagnosis($request)
    {
		$response = false;
		$request['current_version'] = '1.0';
		$request['status'] = '1';

		DB::beginTransaction();


		//DB::transaction(function() use($request)
		//{
			$diagnosis = Diagnosis::create($request->all());
			if($diagnosis){
				$diagnosis_code = $this->generateDiagnosisID($diagnosis->id);
				$diagnosis->update(['code' => $diagnosis_code]);
				//Save diagnosis version
				$this->saveVersion($request->all(),$diagnosis);
				
				//Check if ICD codes exist
				if(!empty($request->icd_code)){
					//Save diagnosis ICD Codes
					$this->addIcdCodes($request->icd_code,$diagnosis);
				}
				$response = true;
			}
			else {
				DB::rollBack();
                return false;
			}
			
		DB::commit();
		//});
		return $response;
    }
    
    //Function to generate Diagnosis ID
    public function generateDiagnosisID($id)
    {
		return Diagnosis::DIAGNOSIS_PREFIX.$id;
    }
    
    //Function to edit specific diagnosis
    public function updateDiagnosis($request,$id)
    {
		$flag = false;
		DB::beginTransaction();
		//DB::transaction(function() use($request,$id)
		//{
			$request->request->add(['current_version' => DB::raw('current_version + 0.1'),'status' => '1']);
			$diagnosis = Diagnosis::where('id',$id)->first();
			$is_updated = $diagnosis->fill($request->except(['_token','_method','icd_code']))->save();
			if($is_updated){
				$diagnosis = Diagnosis::where('id',$id)->first();
				//Save diagnosis version
				$this->saveVersion($request->all(),$diagnosis);
				
				//Check if ICD codes exist
				if(!empty($request->icd_code)){
					//Save diagnosis ICD Codes
					$this->addIcdCodes($request->icd_code,$diagnosis);
				}
			}
			$flag = true;
		//});
		DB::commit();
		return $flag;
    }
    
    //Function to save versions of diagnosis
    public function saveVersion($data,$diagnosis)
    {
		$data['diagnosis_id'] = $diagnosis->id;
		$data['version'] = $diagnosis->current_version;
		$data['code'] = $diagnosis->code;
		$diagnosis_version = new DiagnosisVersion($data);
		//Save ICD codes corresponding to diagnosis
		$diagnosis->versions()->save($diagnosis_version);
	}
	
	//Function to add ICD codes corresponding to diagnosis
	public function addIcdCodes($icd_codes,$diagnosis)
    {
		foreach($icd_codes as $icd_code){
			$diagnosis_icd_codes[] = new DiagnosisIcdCode(['diagnosis_id' => $diagnosis->id,'icd_code_id' => $icd_code,'version' => $diagnosis->current_version]);
		}
		//Save ICD codes corresponding to diagnosis
		$diagnosis->icd_codes()->saveMany($diagnosis_icd_codes);
	}
}



?>
