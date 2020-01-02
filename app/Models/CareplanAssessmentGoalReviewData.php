<?php

namespace App\Models;

use App\Traits\DateFormat;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;


class CareplanAssessmentGoalReviewData extends Model implements Auditable
{

	/* For Audit log*/
    use \OwenIt\Auditing\Auditable;
    use DateFormat;
    protected $guarded = [];
    protected $table = 'assessment_goal_review_data';

    const DRAFT = '0';
    const ACTIVE = '1';

    protected $dates_need_to_be_changed = [
        'created_at',
        'updated_at'
    ];


    // customize the value that save in the audit log
    public function transformAudit(array $data): array
    {
        $patientID = CareplanAssessmentGoalReviewData::find($data['auditable_id'])->patient_id;  
        Arr::set($data, 'patient_id',  $patientID);
        return $data;
    } 
}
