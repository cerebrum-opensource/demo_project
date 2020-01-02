<?php

namespace App\Models;
use App\Traits\Encryptable;
use App\Traits\DateFormat;

use Illuminate\Database\Eloquent\Model;
use DateTime;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Arr;

class Patient extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    use Encryptable;
    //use DateFormat;
    protected $appends = ['state_name', 'case_status_badge', 'registration_status_badge'];

    protected $dontThrowDecryptException = true;
    
    protected $encryptable = [
        'first_name',
        'middle_initial',
        'patient_alias',
        'last_name',
        'email',
        'phone',
        'dob',
        'ssn',
        'emergency_person1_name',
        'emergency_person1_phone',
        'emergency_person1_address',
        'emergency_person1_city',
        'emergency_person1_address2',
        'emergency_person1_relation',
        'emergency_person1_zip',
        'emergency_person2_name',
        'emergency_person2_phone',
        'emergency_person2_address',
        'emergency_person2_relation',
        'emergency_person2_city',
        'emergency_person2_address2',
        'emergency_person2_zip',
        'authorization_code',
        'address_line1',
        'address_line2',
        'city',
        'zip_code',
        'living_with_other_text',
        'patient_concern_other_text',
    ];    

    protected $nonencryptable = [
        'image',
        'case_number',
        'case_status',
        'gender',
        'icd_code',
        'registration_number',
        'registration_status',
        'registered_at',
        'consent_form_date',
        'consent_form_dpoa_name',
        'consent_form_dpoa_phone_number',
        'consent_form_signature_date',
        'consent_form_patient_initials',
        'consent_form_documents_located_at_with',
        'advance_healthcare_on_file',
        'advance_healthcare_checkboxes',
        'advance_healthcare_attorney_name',
        'advance_healthcare_attorney_phone',
        'advance_healthcare_attorney_relation',
        'polst_on_file',
        'polst_checkboxes',
        'patient_functioning',
        'patient_functioning_text',
        'durable_medical_equipment',
        'durable_medical_equipment_text',
        'durable_medical_equipment_other',
        'durable_medical_equipment_other_text',
        'identifying_issues',
        'identifying_issues_text',
        'identifying_issues_other',
        'identifying_issues_other_text',
        'cm_case_status',
        'md_case_status',
        'ed_visits_last_12_months',
        'ed_admissions_last_12_months',
        'pcp_not_required',
        'lives_with_name',
        'patient_concern_name',
        'contract_payer_name',
        'pcp_name',
        'referral_source_name',
        'state_name_log',
        'language_name',
        'hospice_provider_name',
        'home_health_provider_name',
        'icd_code_name',
        'specialist_name',
        'rehab_information_name',
        'housing_assistance_name',
        'mental_health_assistance_name',
    ];   

    protected $casts = [
        'specialist_id' => 'array',
        'substance_abuse' => 'array',
        'patient_functioning' => 'array',
        'durable_medical_equipment' => 'array',
        'identifying_issues' => 'array',
        'patient_concern' => 'array',
        'advance_healthcare_checkboxes' => 'array',
        'polst_checkboxes' => 'array',
        'icd_code' => 'array',
    ];
    protected $dates_need_to_be_changed = [
        'dob',
        'registered_at',
        'created_at',
        'updated_at',
        'consent_form_date',
        'consent_form_signature_date',
        'registered_at',
        'enroll_at',
        'chw_complete_assessment_at',
        'cm_complete_assessment_at',
        'md_complete_assessment_at',
    ];

    public $timestamps = true;

    protected $guarded = ['id','created_at','deleted_at','updated_at'];

    public function patient_data()
    {
        return $this->hasMany('App\Models\PatientData');
    }

    public function refernce()
    {
        return $this->hasOne('App\Models\Registry','id', 'referral_source');
    }
    
    public function county_value()
    {
        return $this->hasOne('App\Models\ManageableField','id', 'county');
    }
    
    public function language_value()
    {
        return $this->hasOne('App\Models\ManageableField','id', 'language');
    }
    
    public function contractPayer()
    {
        return $this->hasOne('App\Models\ContractPayer','id', 'contract_payer');
    }
    
    public function insuranceSecondary()
    {
        return $this->hasOne('App\Models\PatientInsurance')->where('type', 'secondary');
    }
    
    /**
     * Get the PCP Information of the patient
     */ 
    public function pcp_info()
    {
        return $this->belongsTo('App\Models\Registry', 'pcp_id');
    }

    /**
     * Get the Speciality Information of the patient
     */ 
    public function speciality_info($id)
    {
        return Registry::where('id', $id)->where('type', 'specialities')->first();
    }    
 
    /**
     * Get the Housing Assistance Information of the patient
     */ 
    public function housing_info()
    {
        return $this->belongsTo('App\Models\Registry', 'housing_assistance_id');
    }    

    /**
     * Get the Mental Health Assistance Information of the patient
     */ 
    public function mental_health_info()
    {
        return $this->belongsTo('App\Models\Registry', 'mental_health_assistance_id');
    }    

    /**
     * Get the Rehab Information of the patient
     */ 
    public function rehab_info()
    {
        return $this->belongsTo('App\Models\Registry', 'rehab_information_id');
    }

    public function insurancePrimary()
    {
        return $this->hasOne('App\Models\PatientInsurance')->where('type', 'primary');
    }
    
    public function patient_notes()
    {
        return $this->hasMany('App\Models\PatientData')->where('type', 'notes')->orderBy('created_at','desc');
    }

    public function assignedCm()
    {
        return $this->hasOne('App\Models\PatientAssignment')
            ->where('user_type', CASEMANAGER)
            ->where('assignment_type', PatientAssignment::TYPE_HOME_ASSIGNMENT);
    }

    public function assignedChw()
    {
        return $this->hasOne('App\Models\PatientAssignment')
            ->where('user_type', COMMUNITYHEALTHWORKER)
            ->where('assignment_type', PatientAssignment::TYPE_HOME_ASSIGNMENT);
    }

    public function assignedMd()
    {
        return $this->hasOne('App\Models\PatientAssignment')
            ->where('user_type', MANAGERDIRECTOR)
            ->where('assignment_type', PatientAssignment::TYPE_HOME_ASSIGNMENT);
    }


    public function assignedCareplanCm()
    {
        return $this->hasOne('App\Models\PatientAssignment')
            ->where('user_type', CASEMANAGER)
            ->where('assignment_type', PatientAssignment::TYPE_CAREPLAN_ASSIGNMENT);
    }

    public function assignedCareplanChw()
    {
        return $this->hasOne('App\Models\PatientAssignment')
            ->where('user_type', COMMUNITYHEALTHWORKER)
            ->where('assignment_type', PatientAssignment::TYPE_CAREPLAN_ASSIGNMENT);
    }

    public function assignedCareplanMd()
    {
        return $this->hasOne('App\Models\PatientAssignment')
            ->where('user_type', MANAGERDIRECTOR)
            ->where('assignment_type', PatientAssignment::TYPE_CAREPLAN_ASSIGNMENT);
    }

    public function patient_docs()
    {
        return $this->hasMany('App\Models\PatientData')->where('type', 'document')->orderBy('created_at','desc');
    }

    public function insuranceData()
    {
        return $this->hasMany('App\Models\PatientInsurance');
    }
    
    // public function getChwNameAttribute()
    // {
    //     $chw_name = $this->assignment()->where('user_type', 'CHW')->first();
    //     if($chw_name && $chw_name->user)
    //         return $chw_name->user->name;
    //     else
    //         return '-';
    // }    

    // public function getMdNameAttribute()
    // {
    //     $md_name = $this->assignment()->where('user_type', 'MD')->first();
    //     if($md_name && $md_name->user)
    //         return $md_name->user->name;
    //     else
    //         return '-';
    // }    

    // public function getCmNameAttribute()
    // {
    //     $cm_name = $this->assignment()->where('user_type', 'CM')->first();
    //     if($cm_name && $cm_name->user)
    //         return $cm_name->user->name;
    //     else
    //         return '-';
    // }

    // public function getAssignedChwAttribute()
    // {
    //     $chw_name = $this->assignment()->where('user_type', 'CHW')->first();
    //     if($chw_name && $chw_name->user)
    //         return $chw_name->user->id;
    //     else
    //         return '';
    // }     

    // public function getAssignedCmAttribute()
    // {
    //     $cm_name = $this->assignment()->where('user_type', 'CM')->first();
    //     if($cm_name && $cm_name->user)
    //         return $cm_name->user->id;
    //     else
    //         return '';
    // }     

    // public function getAssignedMdAttribute()
    // {
    //     $md_name = $this->assignment()->where('user_type', 'MD')->first();
    //     if($md_name && $md_name->user)
    //         return $md_name->user->id;
    //     else
    //         return '';
    // }  

    public function getCaseStatusAttribute($value)
    {
        $status='';
        switch ($value) {
        case REFERRAL_STATUS_OPEN:
            $status = 'Open';
            break;
        case REFERRAL_STATUS_PRE_REGISTER:
            $status = 'Pre-Reg';
            break;
        case REFERRAL_STATUS_REGISTER:
            $status = 'Register';
            break;
        case REFERRAL_STATUS_REJECTED:
            $status = 'Rejected';
            break;
        case REFERRAL_STATUS_MATURE:
            $status = 'Mature';
            break;
        default:
            $status = 'Open';
            break;
        }
        return $status;
    }
    
    public function scopeReferral($query)
    { 
        $caseStatus = [REFERRAL_STATUS_OPEN,REFERRAL_STATUS_PRE_REGISTER,REFERRAL_STATUS_REJECTED];
        return $query->whereIn('case_status', $caseStatus)->orderBy('created_at','desc');
    }    

    public function scopeRegistration($query)
    { 
        return $query->where('case_status', REFERRAL_STATUS_REGISTER)->orderBy('registered_at','desc');
    }

    public function scopeCaseload($query)
    { 
        return $query->where('case_status', REFERRAL_STATUS_ENROLL)->orderBy('enroll_at','desc');
    }

    /*public function scopeAssignedRegistration($query, $id, $type = null)
    { 
        $caseStatus = [2,3,4];
        return $query->whereIn('case_status', $caseStatus)->orderBy('registered_at','desc');
    }*/
    
  /*  public function getAgeAttribute()
        {
            $d1 = new DateTime($this->attributes['dob']);
            $d2 = new DateTime();
            $interval = $d1->diff($d2);
            $diff_in_days = $interval->format('%y years');
            return $diff_in_days;
        }*/
    /*public function getSnnNumberAttribute()
        {
            $strring = substr($this->attributes['ssn'], 4);
            return '****'.$strring;
        }*/
    /*public function getNameAttribute()
        {
            $strring = $this->attributes['first_name'].' '.$this->attributes['last_name'];
            return $strring;
        }*/
    public function getCaseStatusValueAttribute()
    {
        return $this->attributes['case_status'];
    }

     public function getStateNameAttribute()
    {
       $state =  $this->state;  
       return $state->full_name;    
    }     

    public function getCaseStatusBadgeAttribute()
    {
       $status='';
        switch ($this->attributes['case_status']) {
        case REFERRAL_STATUS_OPEN:
            $status = '<span class="badge badge-warning">Open</span>';
            break;
        case REFERRAL_STATUS_PRE_REGISTER:
            $status = '<span class="badge badge-info">Pre-Reg</span>';
            break;
        case REFERRAL_STATUS_REGISTER:
            $status = '<span class="badge badge-primary">Register</span>';
            break;
        case REFERRAL_STATUS_REJECTED:
            $status = '<span class="badge badge-danger">Rejected</span>';
            break;
        case REFERRAL_STATUS_MATURE:
            $status = '<span class="badge badge-success">Mature</span>';
            break;
        default:
            $status = '<span class="badge badge-warning">Open</span>';
            break;
        }
        return $status;
    } 
    
    public function getCmAssessmentCaseStatusBadgeAttribute()
    {
       $status='';
        switch ($this->attributes['cm_case_status']) {
        case ASSESMENT_PENDING:
            $status = '<span class="pat-status pending-status">Pending</span>';
            break;
        case ASSESMENT_INCOMPLETE:
            $status = '<span class="pat-status in-complete-status">Incomplete</span>';
            break;
        case ASSESMENT_COMPLETED:
            $status = '<span class="pat-status">Completed</span>';
            break;
        case ASSESMENT_REJECTED:
            $status = '<span class="pat-status rejected-status">Rejected</span>';
            break;
        default:
            $status = '<span class="pat-status pending-status">Pending</span>';
            break;
        }
        return $status;
    }    

    public function getMdAssessmentCaseStatusBadgeAttribute()
    {
       $status='';
        switch ($this->attributes['md_case_status']) {
        case ASSESMENT_PENDING:
            $status = '<span class="pat-status pending-status">Pending</span>';
            break;
        case ASSESMENT_INCOMPLETE:
            $status = '<span class="pat-status in-complete-status">Incomplete</span>';
            break;
        case ASSESMENT_COMPLETED:
            $status = '<span class="pat-status">Completed</span>';
            break;
        case ASSESMENT_REJECTED:
            $status = '<span class="pat-status rejected-status">Rejected</span>';
            break;
        default:
            $status = '<span class="pat-status pending-status">Pending</span>';
            break;
        }
        return $status;
    }    

    public function getChwAssessmentCaseStatusBadgeAttribute()
    {
       $status='';
        switch ($this->attributes['chw_case_status']) {
        case ASSESMENT_PENDING:
            $status = '<span class="pat-status pending-status">Pending</span>';
            break;
        case ASSESMENT_INCOMPLETE:
            $status = '<span class="pat-status in-complete-status">Incomplete</span>';
            break;
        case ASSESMENT_COMPLETED:
            $status = '<span class="pat-status">Completed</span>';
            break;
        case ASSESMENT_REJECTED:
            $status = '<span class="pat-status rejected-status">Rejected</span>';
            break;
        default:
            $status = '<span class="pat-status pending-status">Pending</span>';
            break;  
        }
        return $status;
    }

    public function getRegistrationStatusBadgeAttribute()
    {
       $status='';
        switch ($this->attributes['registration_status']) {
        case REGISTRATION_STATUS_NEW:
            $status = '<span class="badge badge-secondary">New</span>';
            break;
        case REGISTRATION_STATUS_INCOMPLETE:
            $status = '<span class="badge badge-warning">Incomplete</span>';
            break;
        case REGISTRATION_STATUS_COMPLETED:
            $status = '<span class="badge badge-success">Completed</span>';
            break;
        case REGISTRATION_STATUS_REJECTED:
            $status = '<span class="badge badge-danger">Rejected</span>';
            break;
        default:
            $status = '<span class="badge badge-secondary">New</span>';
            break;
        }
        return $status;
    } 

    public function getChwCaseStatusBadgeAttribute()
    {
       $status='';
        switch ($this->attributes['chw_case_status']) {
        case ASSESMENT_PENDING:
            $status = '<span class="badge badge-secondary">Pending</span>';
            break;
        case ASSESMENT_INCOMPLETE:
            $status = '<span class="badge badge-warning">Incomplete</span>';
            break;
        case ASSESMENT_COMPLETED:
            $status = '<span class="badge badge-success">Completed</span>';
            break;
        case ASSESMENT_REJECTED:
            $status = '<span class="badge badge-danger">Rejected</span>';
            break;
        default:
            $status = '<span class="badge badge-secondary">Pending</span>';
            break;
        }
        return $status;
    } 

    public function getCmCaseStatusBadgeAttribute()
    {
       $status='';
        switch ($this->attributes['cm_case_status']) {
        case '0':
            $status = '<span class="badge badge-secondary">Pending</span>';
            break;
        case '1':
            $status = '<span class="badge badge-warning">Incomplete</span>';
            break;
        case '2':
            $status = '<span class="badge badge-success">Completed</span>';
            break;
        case '3':
            $status = '<span class="badge badge-danger">Rejected</span>';
            break;
        default:
            $status = '<span class="badge badge-secondary">Pending</span>';
            break;
        }
        return $status;
    }

    public function getMdCaseStatusBadgeAttribute()
    {
       $status='';
        switch ($this->attributes['md_case_status']) {
        case ASSESMENT_PENDING:
            $status = '<span class="badge badge-secondary">Pending</span>';
            break;
        case ASSESMENT_INCOMPLETE:
            $status = '<span class="badge badge-warning">Incomplete</span>';
            break;
        case ASSESMENT_COMPLETED:
            $status = '<span class="badge badge-success">Completed</span>';
            break;
        case ASSESMENT_REJECTED:
            $status = '<span class="badge badge-danger">Rejected</span>';
            break;
        default:
            $status = '<span class="badge badge-secondary">Pending</span>';
            break;
        }
        return $status;
    } 

    public function state()
    {
        return $this->belongsTo('App\Models\State');
    }

    public function getCmConsentFormStatusBadgeAttribute()
    {
        $status='';
        if($this->attributes['patient_decision'] == CONSENT_FORM_ACCEPT && $this->attributes['consent_form_signed'] != null && $this->attributes['consent_form_signed'] != '')
            $status = '<span class="pat-status">Completed</span>';
        elseif($this->attributes['patient_decision'] == CONSENT_FORM_REFUSE)
            $status = '<span class="pat-status rejected-status">Rejected</span>';
        else
            $status = '<span class="pat-status pending-status">Pending</span>';
        
        return $status;
    }       
        

    public function getEnrollmentStatusBadgeAttribute()
    {
       $status='';
        switch ($this->attributes['enrollment_status']) {
        case ENROLLMENT_STATUS_INTENSE:
            $status = '<span class="badge badge-secondary">Intense</span>';
            break;
        case ENROLLMENT_STATUS_MATURE:
            $status = '<span class="badge badge-warning">Mature</span>';
            break;
        case ENROLLMENT_STATUS_GRADUATE:
            $status = '<span class="badge badge-success">Graduate</span>';
            break;
        case ENROLLMENT_STATUS_DISCHARGE:
            $status = '<span class="badge badge-danger">Discharge</span>';
            break;
        case ENROLLMENT_STATUS_ELOPED:
            $status = '<span class="badge badge-danger">Eloped</span>';
            break;
        default:
            $status = '<span class="badge badge-secondary">Intense</span>';
            break;
        }
        return $status;
    }     



    // funciton to get array of key that is encrypted
    public function getEncryptableValue()
    {
        return $this->encryptable;
    }

    // funciton to get array of key that is not encrypted

    public function getNonEncryptableValue()
    {
        return $this->nonencryptable;
    }

    // customiza the value that save in the audit log
    public function transformAudit(array $data): array
    {
        

    
        Arr::set($data, 'patient_id',  $data['auditable_id']);

        // update the lives with name in log table
        if (Arr::has($data, 'new_values.lives_with')) {
            $data['old_values']['lives_with_name'] = Arr::has($data, 'old_values.lives_with') && ManageableField::find($data['old_values']['lives_with']) ? ManageableField::find($data['old_values']['lives_with'])->name : '';
            $data['new_values']['lives_with_name'] = ManageableField::find($data['new_values']['lives_with']) ? ManageableField::find($data['new_values']['lives_with'])->name : '';
        }
        // update the patient concern name in log table
        if (Arr::has($data, 'new_values.patient_concern')) {  
            $newPatientConcerns = ManageableField::whereIn('id', json_decode($data['new_values']['patient_concern']))->pluck('name','id')->toArray();
            $oldPatientConcerns =Arr::has($data, 'old_values.patient_concern') && !empty($data['old_values']['patient_concern'])? ManageableField::whereIn('id', json_decode($data['old_values']['patient_concern']))->pluck('name','id')->toArray() : array();
            $data['new_values']['patient_concern_name'] =implode(',',$newPatientConcerns);
            $data['old_values']['patient_concern_name'] = implode(',',$oldPatientConcerns);

        }
        // update the icd code name in log table
        if (Arr::has($data, 'new_values.icd_code')) {  
            $newPatientConcerns = IcdCode::whereIn('id', json_decode($data['new_values']['icd_code']))->pluck('code','id')->toArray();
            $oldPatientConcerns =Arr::has($data, 'old_values.icd_code') && !empty($data['old_values']['icd_code'])? IcdCode::whereIn('id', json_decode($data['old_values']['icd_code']))->pluck('code','id')->toArray() : array();
            $data['new_values']['icd_code_name'] = implode(',',$newPatientConcerns);
            $data['old_values']['icd_code_name'] = implode(',',$oldPatientConcerns);

        }
        // update contract payer name in log table 
        if (Arr::has($data, 'new_values.contract_payer')) {  
            $data['old_values']['contract_payer_name'] = Arr::has($data, 'old_values.contract_payer') && Registry::find($data['old_values']['contract_payer'])? Registry::find($data['old_values']['contract_payer'])->name : '';
            $data['new_values']['contract_payer_name'] = Registry::find($data['new_values']['contract_payer']) ? Registry::find($data['new_values']['contract_payer'])->name : '';

        }
        // update the pcp name in log table
        if (Arr::has($data, 'new_values.pcp_id')) {  
            $data['old_values']['pcp_name'] = Arr::has($data, 'old_values.pcp_id') && Registry::find($data['old_values']['pcp_id']) ? Registry::find($data['old_values']['pcp_id'])->name : '';
            $data['new_values']['pcp_name'] = Registry::find($data['new_values']['pcp_id']) ? Registry::find($data['new_values']['pcp_id'])->name : '';

        }
        // update the referral source name in log table
        if (Arr::has($data, 'new_values.referral_source')) {  
            $data['old_values']['referral_source_name'] = Arr::has($data, 'old_values.referral_source') && Registry::find($data['old_values']['referral_source'])? Registry::find($data['old_values']['referral_source'])->org_name : '';
            $data['new_values']['referral_source_name'] = Registry::find($data['new_values']['referral_source']) ? Registry::find($data['new_values']['referral_source'])->org_name : '';
        }
        // update the state name in log table
        if (Arr::has($data, 'new_values.state_id')) {  
            $data['old_values']['state_name_log'] = Arr::has($data, 'old_values.state_id') ? State::find($data['old_values']['state_id'])->full_name : '';
            $data['new_values']['state_name_log'] = State::find($data['new_values']['state_id']) ? State::find($data['new_values']['state_id'])->full_name : '';
        }

        // update the language in log table
        if (Arr::has($data, 'new_values.language')) {  
            $data['old_values']['language_name'] = Arr::has($data, 'old_values.language') && ManageableField::find($data['old_values']['language'])  ? ManageableField::find($data['old_values']['language'])->name : '';
            $data['new_values']['language_name'] = ManageableField::find($data['new_values']['language']) ? ManageableField::find($data['new_values']['language'])->name : '';
        }

        // update the home health provider name in log table
        if (Arr::has($data, 'new_values.home_health_provider_id')) {  
            $data['old_values']['home_health_provider_name'] = Arr::has($data, 'old_values.home_health_provider_id') && Registry::find($data['old_values']['home_health_provider_id']) ? Registry::find($data['old_values']['home_health_provider_id'])->org_name : '';
            $data['new_values']['home_health_provider_name'] = Registry::find($data['new_values']['home_health_provider_id']) ? Registry::find($data['new_values']['home_health_provider_id'])->org_name : '';

        }

        // update the hospice provider name in log table
        if (Arr::has($data, 'new_values.hospice_provider_id')) {  
            $data['old_values']['hospice_provider_name'] = Arr::has($data, 'old_values.hospice_provider_id') && Registry::find($data['old_values']['hospice_provider_id']) ? Registry::find($data['old_values']['hospice_provider_id'])->org_name : '';
            $data['new_values']['hospice_provider_name'] = Registry::find($data['new_values']['hospice_provider_id']) ? Registry::find($data['new_values']['hospice_provider_id'])->org_name : '';

        }
        // update the icd code name in log table
        if (Arr::has($data, 'new_values.specialist_id')) {  
            $newPatientConcerns =Arr::has($data, 'new_values.specialist_id') && !empty($data['new_values']['specialist_id'])? PcpInformation::whereIn('id', json_decode($data['new_values']['specialist_id']))->pluck('doctor_name','id')->toArray() : array();
            $oldPatientConcerns =Arr::has($data, 'old_values.specialist_id') && !empty($data['old_values']['specialist_id'])? PcpInformation::whereIn('id', json_decode($data['old_values']['specialist_id']))->pluck('doctor_name','id')->toArray() : array();
            $data['new_values']['specialist_name'] = implode(',',$newPatientConcerns);
            $data['old_values']['specialist_name'] = implode(',',$oldPatientConcerns);

        }
        // update the rehab_information_name  in log table
        if (Arr::has($data, 'new_values.rehab_information_id')) {  
            $data['old_values']['rehab_information_name'] = Arr::has($data, 'old_values.rehab_information_id') && Registry::find($data['old_values']['rehab_information_id']) ? Registry::find($data['old_values']['rehab_information_id'])->org_name : '';
            $data['new_values']['rehab_information_name'] = Registry::find($data['new_values']['rehab_information_id']) ? Registry::find($data['new_values']['rehab_information_id'])->org_name : '';

        }
        // update the housing_assistance_name  in log table
        if (Arr::has($data, 'new_values.housing_assistance_id')) {  
            $data['old_values']['housing_assistance_name'] = Arr::has($data, 'old_values.housing_assistance_id') && Registry::find($data['old_values']['housing_assistance_id']) ? Registry::find($data['old_values']['housing_assistance_id'])->org_name : '';
            $data['new_values']['housing_assistance_name'] = Registry::find($data['new_values']['housing_assistance_id']) ? Registry::find($data['new_values']['housing_assistance_id'])->org_name : '';

        }
        // update the mental_health_assistance_name  in log table
        if (Arr::has($data, 'new_values.mental_health_assistance_id')) {  
            $data['old_values']['mental_health_assistance_name'] = Arr::has($data, 'old_values.mental_health_assistance_id') && Registry::find($data['old_values']['mental_health_assistance_id']) ? Registry::find($data['old_values']['mental_health_assistance_id'])->org_name : '';
            $data['new_values']['mental_health_assistance_name'] = Registry::find($data['new_values']['mental_health_assistance_id']) ? Registry::find($data['new_values']['mental_health_assistance_id'])->org_name : '';

        }
        return $data;
    }

    // get the patient call
    public function patient_call()
    {
        return $this->hasMany('App\Models\PatientPhoneCall');
    }

    // get the all type patient assessment
    public function patient_assessment()
    {
        return $this->hasMany('App\Models\PatientAssessment');
    }

    // get the patient assessment
    public function patient_assessment_comment()
    {
        return $this->hasMany('App\Models\PatientAssessment')
        ->where('comment_type', '!=', 'consent_rejection')->with('user');
        
    }

    // get the patient assignment
    public function patient_assignment()
    {
        return $this->hasMany('App\Models\PatientAssignment');
    }

    // get the patient assignment
    public function patient_allergies()
    {
        return $this->hasMany('App\Models\PatientAllergy');
    }


    // get the patient assignment
    public function patient_medications()
    {
        return $this->hasMany('App\Models\PatientMedication');
    }


    public function replicateAssignmentForCareplan()
    {
        $chwAssignment = $this->assignedChw;
        $cloneChwAssignment = $chwAssignment->replicate();
        $cloneChwAssignment->assignment_type = PatientAssignment::TYPE_CAREPLAN_ASSIGNMENT;
        $cloneChwAssignment->save();

        $cmAssignment = $this->assignedCm;
        $cloneCmAssignment = $cmAssignment->replicate();
        $cloneCmAssignment->assignment_type = PatientAssignment::TYPE_CAREPLAN_ASSIGNMENT;
        $cloneCmAssignment->save();

        $mdAssignment = $this->assignedMd;
        $cloneMdAssignment = $mdAssignment->replicate();
        $cloneMdAssignment->assignment_type = PatientAssignment::TYPE_CAREPLAN_ASSIGNMENT;
        $cloneMdAssignment->save();

    }

    // get the case status for log

    public function getLogStatus($statusId)
    {
       $status='';
        switch ($statusId) {
        case REFERRAL_STATUS_OPEN:
            $status = 'New';
            break;
        case REFERRAL_STATUS_PRE_REGISTER:
            $status = 'Incomplete';
            break;
        case REFERRAL_STATUS_REGISTER:
            $status = 'Completed';
            break;
        case REFERRAL_STATUS_REJECTED:
            $status = 'Rejected';
            break;
        default:
            $status = 'New';
            break;
        }
        return $status;
    }

    public function getEnrollmentStatusNameAttribute($value)
    {
        $status='';
        switch ($this->attributes['enrollment_status']) {
        case ENROLLMENT_STATUS_INTENSE:
            $status = 'Intense';
            break;
        case ENROLLMENT_STATUS_MATURE:
            $status = 'Mature';
            break;
        case ENROLLMENT_STATUS_GRADUATE:
            $status = 'Graduate';
            break;
        case ENROLLMENT_STATUS_DISCHARGE:
            $status = 'Discharge';
            break;
        case ENROLLMENT_STATUS_ELOPED:
            $status = 'Eloped';
            break;
        default:
            $status = 'Intense';
            break;
        }
        return $status;
    }

    public function consentSignature()
    {
        return $this->hasMany('App\Models\PatientSignature')->where('type', '0');
    } 

    public function consentFormData()
    {
        return $this->hasMany('App\Models\PatientForm')->where('type', '0');
    }

    public function hippaFormData()
    {
        return $this->hasMany('App\Models\PatientForm')->where('type', '1');
    }

    public function hippaSignature()
    {
        return $this->hasMany('App\Models\PatientSignature')->where('type', '1');
    } 
}
