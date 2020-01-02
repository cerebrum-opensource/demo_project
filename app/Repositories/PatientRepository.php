<?php

namespace App\Repositories;

class PatientRepository
{
	public function generateCaseNumber($patient_id)
	{
		$number = 1000 + $patient_id;
		return "WPR".$number;
	}	

	public function generateRegistrationNumber($patient_id)
	{
		$number = 1000 + $patient_id;
		return "WPN".$number;
	}
}