<?php

namespace App\Repositories;

use App\Models\{ManageableField,IcdCode};

class CommonRepository
{
	//Function to get list of metrices
	public static function getMetrices()
	{
		return ManageableField::where('type','metric')->where('status',1)->pluck('name','id');
	}
	
	//Function to display ICD codes in diagnosis listing
	public static function getIcdCodesForDiagnosis($icd_codes)
	{
		$active = ''; 
        if(!empty($icd_codes)){
			foreach($icd_codes AS $val){
				$active .= IcdCode::where('id',$val->icd_code_id)->value('code').", ";
			}
            return trim($active,', ');
        }
        else
        {
            return false;
        }
	}


}
