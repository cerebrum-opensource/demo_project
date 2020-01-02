<?php

use App\Models\ManageableField;
use App\Models\State;

if (!function_exists('encryption')) {
    
    function encryption(string $message, string $salt): string
    {
        $decryptKey = decrypt($salt);
        $decryptKeyReverse = strrev($decryptKey);
        
        $key1 = $decryptKeyReverse[0].$decryptKey[0].substr($decryptKeyReverse, 3,2).$decryptKeyReverse[1].$decryptKey[1].substr($decryptKey, 3,2).$decryptKeyReverse[7].$decryptKeyReverse[4].substr($decryptKeyReverse, 5,2).substr($decryptKey, 4,2).$decryptKey[1].$decryptKeyReverse[2];
        $key2 = substr($key1, 5,2).$decryptKeyReverse[3].substr($decryptKeyReverse, 5,2).$key1[12].$key1[14].substr($key1, 9,2).$decryptKey[4].substr($key1, 14,2).$decryptKeyReverse[4].substr($decryptKey, 4,2).$key1[11];
        
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        
        $cipher = base64_encode(
            $nonce.
            sodium_crypto_secretbox(
                $message,
                $nonce,
                $key2.$key1
            )
        );
        
        return $cipher;        
    }
}
 
if (!function_exists('decryption')) {
     
    function decryption(string $encrypted, string $salt): string
    {
        $decoded = base64_decode($encrypted);
        $nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
        $ciphertext = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');
        
        $decryptKey = decrypt($salt);
        $decryptKeyReverse = strrev($decryptKey);
        
        $key1 = $decryptKeyReverse[0].$decryptKey[0].substr($decryptKeyReverse, 3,2).$decryptKeyReverse[1].$decryptKey[1].substr($decryptKey, 3,2).$decryptKeyReverse[7].$decryptKeyReverse[4].substr($decryptKeyReverse, 5,2).substr($decryptKey, 4,2).$decryptKey[1].$decryptKeyReverse[2];
        $key2 = substr($key1, 5,2).$decryptKeyReverse[3].substr($decryptKeyReverse, 5,2).$key1[12].$key1[14].substr($key1, 9,2).$decryptKey[4].substr($key1, 14,2).$decryptKeyReverse[4].substr($decryptKey, 4,2).$key1[11];

        $plain = sodium_crypto_secretbox_open(
            $ciphertext,
            $nonce,
            $key2.$key1
        );
        if (!is_string($plain)) {
            throw new Exception('Invalid MAC');
        }
        return $plain;
    }
}

if (!function_exists('salt')) {
     
    function salt(string $salt): string
    {
        for($i=0;$i<strlen($salt);$i=$i+2){
         $tmp = $salt[$i];
         $salt[$i] = $salt[$i+1];
         $salt[$i+1] = $tmp;
        }
        $salt = sortData($salt,2);
        $salt = sortData($salt,4);
        $salt = sortData($salt,6);
        $salt = sortData($salt,8);
        $salt = sortData($salt,10);
        $salt = sortData($salt,12);
        $salt = sortData($salt,14);
        $salt = sortData($salt,16);
        $salt = sortData($salt,16);
        return $salt;
    }
}

if (!function_exists('sortData')) {
     
    function sortData(string $str,$number)
    {
        for($i=0;$i<strlen($str)/2;$i=$i+2){
            $newstring=$str;
            $stringPosition=strlen($str)-($i+$number);
            $tt=$i;
            for($j=$stringPosition;$j<$stringPosition+$number;$j++){
                $ne=$str[$tt];
                $str[$tt]=$str[$j];
                $str[$j]=$ne;
                $tt++;
            }
         }
        return $str;
    }
}

if (!function_exists('relationship_array')) {
     
    function relationship_array()
    {
        $common_array = array("Father"=>"Father", "Mother"=>"Mother", "Brother"=>"Brother", "Sister"=>"Sister", "Uncle"=>"Uncle", "Aunt"=>"Aunt", "Grandfather"=>"Grandfather", "Grandmother"=>"Grandmother", "Other"=>"Other");
        return $common_array;
    }
}

if (!function_exists('relationship_array_for_validation')) {
     
    function relationship_array_for_validation()
    {
        $common_array = array(""=>"", "Father"=>"Father", "Mother"=>"Mother", "Brother"=>"Brother", "Sister"=>"Sister", "Uncle"=>"Uncle", "Aunt"=>"Aunt", "Grandfather"=>"Grandfather", "Grandmother"=>"Grandmother", "Other"=>"Other");
        return $common_array;
    }
}

if (!function_exists('change_date_format')) {
     
    function change_date_format($date)
    {
        if($date) {
            $formatDate = explode('-',$date);
            $formatDate = $formatDate[2].'-'.$formatDate[0].'-'.$formatDate[1];
            return $formatDate;
        }

    }
}

if (!function_exists('create_date_format')) {

    function create_date_format($date, $currentFormat, $requiredFormat='m-d-Y')
    {
        if($date) {
            if(!$_COOKIE['client_timezone']){
                $timezone=Config::get('app.timezone');
            }
            else {
                $timezone=$_COOKIE['client_timezone'];
            }
            return \Carbon\Carbon::createFromFormat($currentFormat,$date)->timezone($timezone)->format($requiredFormat);
        }

    }
}

if (!function_exists('phone_number_format')) {
     
    function phone_number_format($phone_number)
    {
        $result = "(".substr($phone_number, 0, 3).") ".substr($phone_number, 3, 3)."-".substr($phone_number, 6, 4);
        return $result;
    }
}

if (!function_exists('change_phone_format')) {
     
    function change_phone_format($phone)
    {
        $phonePart = substr($phone, 0, 3);
        $phonePart2 = substr($phone, 3, 3);
        $phonePart3 = substr($phone, 6, 4);
        $phone = '('.$phonePart.')-'.$phonePart2.'-'.$phonePart3;
        return $phone;
    }
}


if (!function_exists('setting_field_type')) {
     
    function setting_field_type()
    {
        $common_array = array("patient_concern"=>"Patient Concern", "lives_with"=>"Lives With", "document_category"=>"Document Category", "language"=>"Language");

        return $common_array;
    }
}
if (!function_exists('speciality_array')) {
     
    function speciality_array()
    {
        $common_array = array("Allergy"=>"Allergy", "Anesthesiology"=>"Anesthesiology","Otolaryngology"=>"Otolaryngology");
        return $common_array;
    }
}


if (!function_exists('gender_array')) {
     
    function gender_array()
    {
        return array("Male"=>"Male", "Female"=>"Female","Others"=>"Others");
    }
}

if(!function_exists('get_states')) {

    function get_states()
    {
        return State::all()->pluck('full_name','id')->prepend('Please select', '')->toArray();
    }
}

if(!function_exists('get_languages')) {

    function get_languages()
    {
        return ManageableField::where('type','language')->pluck('name','id')->prepend('Please select', '')->toArray();
    }
}

if(!function_exists('get_manageable_fields')) {

    function get_manageable_fields($type)
    {
        return ManageableField::where('type',$type)->pluck('name','id')->prepend('Please select', '')->toArray();
    }
}




if (!function_exists('return_log_model')) {
     
    function return_log_model($modal_name)
    {

        $name='';
        switch ($modal_name) {
        case 'App\Models\PatientData':
            $name = 'Data';
            break;
        case 'App\Models\PatientInsurance':
            $name = 'Insurance';
            break;
        case 'App\Models\PatientAssignment':
            $name = 'Assignment';
            break;
        case 'App\Models\Patient':
            $name = 'Info';
            break;
        case 'App\Models\PatientAssessment':
            $name = 'Assessment';
            break;
        case 'App\Models\PatientPhoneCall':
            $name = 'Phone Call';
            break;   
        case 'App\Models\PatientAllergy':
            $name = 'Allergy';
            break;
        case 'App\Models\PatientMedication':
            $name = 'Medication';
            break;             
        default:
            $name = '';
            break;
        }
        return $name;
    }
}

if (!function_exists('return_comment_type_log')) {
     
    function return_comment_type_log($comment_type)
    {

        $name='';
        switch ($comment_type) {
        case 'assessment_rejection':
            $name = 'Not Ready for Assessment';
            break;
        case 'assessment':
            $name = 'Assessment';
            break;
        case 'consent_rejection':
            $name = 'Not Ready to Sign Consent form';
            break;
        default:
            $name = '';
            break;
        }
        return $name;
    }
}

if (!function_exists('static_check_box_for_advanced_directive')) {
     
    function static_check_box_for_advanced_directive()
    {
        $common_array = array("full_code"=>"full_code", "dnr"=>"dnr","limited_treatment"=>"limited_treatment");
        return $common_array;
    }
}
if (!function_exists('static_check_box_for_patient_functioning')) {
     
    function static_check_box_for_patient_functioning()
    {
        $common_array = array("independent"=>"independent", "independent_with_dme"=>"independent_with_dme","hired_caregivers_ihss"=>"hired_caregivers_ihss","family_able_to_assist"=>"family_able_to_assist","family_unable_to_assist"=>"family_unable_to_assist");
        return $common_array;
    }
}
if (!function_exists('static_check_box_for_medical_equipment')) {
     
    function static_check_box_for_medical_equipment()
    {
        $common_array = array("fww"=>"fww", "wheelchair"=>"wheelchair","cane"=>"cane","bedside_commode"=>"bedside_commode","other"=>"other");
        return $common_array;
    }
}
if (!function_exists('static_check_box_for_identifying_issues')) {
     
    function static_check_box_for_identifying_issues()
    {
        $common_array = array("age_with_critical_factors"=>"age_with_critical_factors", "homeless"=>"homeless","financial"=>"financial","new_major_diagnosis"=>"new_major_diagnosis","no_pcp"=>"no_pcp","domestic_violence"=>"domestic_violence","change_in_functional_status"=>"change_in_functional_status","loss_and_grief"=>"loss_and_grief","substance_abuse"=>"substance_abuse","end_of_life"=>"end_of_life","mental_health"=>"mental_health","complex_placement"=>"complex_placement","other"=>"other");
        return $common_array;
    }
}

if (!function_exists('static_medicine_name')) {
     
    function static_medicine_name()
    {
        $common_array = array("medicine_1"=>"medicine_1", "medicine_2"=>"medicine_2");
        return $common_array;
    }
}

if (!function_exists('static_unit_name')) {
     
    function static_unit_name()
    {
        $common_array = array("unit_1"=>"unit_1", "unit_2"=>"unit_2");
        return $common_array;
    }
}

if (!function_exists('static_allergy_name')) {
     
    function static_allergy_name()
    {
        $common_array = array("allergy_1"=>"allergy_1", "allergy_2"=>"allergy_2");
        return $common_array;
    }
}

if (!function_exists('static_reaction_name')) {
     
    function static_reaction_name()
    {
        $common_array = array("reaction_1"=>"reaction_1", "reaction_2"=>"reaction_2");
        return $common_array;
    }
}

if (!function_exists('static_substance_name')) {
     
    function static_substance_name()
    {
        $common_array = array("etoh"=>"etoh", "methamphetamines"=>"methamphetamines","opiates"=>"opiates", "benzodiazepines"=>"benzodiazepines", "marijuana"=>"marijuana");
        return $common_array;
    }
}

if (!function_exists('static_severity_name')) {
     
    function static_severity_name()
    {
        $common_array = array("mild"=>"mild", "severe"=>"severe");
        return $common_array;
    }
}

if (!function_exists('get_dob_in_years')) {
     
    function get_dob_in_years($date)
    {

        $final_age = '';
       
        if($date){
            $dob = explode('-',$date);
            $dob = $dob[1].'-'.$dob[0].'-'.$dob[2];
            $d1 = new DateTime($dob);
            $d2 = new DateTime();
            $interval = $d1->diff($d2);
            $final_age = $diff_in_days = $interval->format('%y Yrs');
        }
        return $final_age;
    }
}

if (!function_exists('registry_types')) {
    function get_registry_type($key = '')
    {
        $types = [
            'contract_payers' => 'Contract Payer',
            'insurances' => 'Insurance',
            'pcp_informations' => 'PCP Information',
            'referral_sources' => 'Referral Source',
            'emergency_departments' => 'Emergency Department',
            'rehabs' => 'Rehab Information',
            'hospice_providers' => 'Hospice Provider',
            'specialities' => 'Specialties',
            'housing_assistances' => 'Housing Assistance',
            'mental_health_assistances' => 'Mental Health Assistance',
            'home_health_providers' => 'Home Health Provider'
        ];

        if(isset($types[$key])) {
            return $types[$key];
        }

        return '';
    }
}


if (!function_exists('registry_type1')) {
     
    function registry_type1()
    {
        $common_array = array("insurances"=>"insurances", "emergency_departments"=>"emergency_departments","rehabs"=>"rehabs", "housing_assistances"=>"housing_assistances","mental_health_assistances"=>"mental_health_assistances", "home_health_providers"=>"home_health_providers","hospice_providers"=>"hospice_providers");
        return $common_array;
    }
}

if (!function_exists('tool_type')) {
     
    function tool_type()
    {
        $common_array = array("Online"=>"Online", "Pdf"=>"Pdf");

        return $common_array;
    }
}

if (!function_exists('encrypt_decrypt')) {
    
    function encrypt_decrypt($action, $string) {
        $output = false;
        $encrypt_method = "AES-256-CBC";
        $secret_key = env('SECRET_KEY', 'wiw3g716qXYY29HUzzdOtvSfNkb7n5PN');
        $secret_iv = env('SECRET_IV', 'kIksnotLbVZ71hW4mtnL4RFSyar3l6a8');
        // hash
        $key = hash('sha256', $secret_key);     
        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', $secret_iv), 0, 16);
        
        if ( $action == 'encrypt' ) {
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        } else if( $action == 'decrypt' ) {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }
        return $output;
    }

}

if (!function_exists('is_flag')) {
     
    function is_flag()
    {
        $common_array = array("0"=>"No", "1"=>"Yes");

        return $common_array;
    }
}

if (!function_exists('goal_type')) {
     
    function goal_type()
    {
        $common_array = array("0"=>"Qualitative", "1"=>"Quantative");

        return $common_array;
    }
}

if (!function_exists('enrollment_status')) {
     
    function enrollment_status()
    {
       // $common_array = array(ENROLLMENT_STATUS_INTENSE => "Intense", ENROLLMENT_STATUS_MATURE =>"Mature",ENROLLMENT_STATUS_GRADUATE => "Graduate", ENROLLMENT_STATUS_DISCHARGE =>"Discharge", ENROLLMENT_STATUS_ELOPED =>"Eloped");
        $common_array = array(ENROLLMENT_STATUS_INTENSE => "Intense", ENROLLMENT_STATUS_MATURE =>"Mature",ENROLLMENT_STATUS_GRADUATE => "Graduate");
        return $common_array;
    }
}

if (!function_exists('enrollment_status_for_registration')) {
     
    function enrollment_status_for_registration()
    {
        $common_array = array(ENROLLMENT_STATUS_INTENSE => "Intense", ENROLLMENT_STATUS_MATURE =>"Mature");

        return $common_array;
    }
}

if (!function_exists('date_format_change')) {
     
    function date_format_change($date)
    {
        if(!$_COOKIE['client_timezone']){
            $timezone=Config::get('app.timezone');
        }
        else {
            $timezone=$_COOKIE['client_timezone'];
        }
        $date = \Carbon\Carbon::parse($date)->timezone($timezone)->format('m-d-Y');
        return $date;
    }
}

if (!function_exists('tzDate')) {
    function tzDate($date)
    {
        if(!$_COOKIE['client_timezone']){
            $timezone=Config::get('app.timezone');
        }
        else {
            $timezone=$_COOKIE['client_timezone'];
        }

        return $date->timezone($timezone);
    }
}





if (!function_exists('return_user_type')) {
     
    function return_user_type($user_type)
    {

        $name='';
        switch ($user_type) {
        case COMMUNITYHEALTHWORKER:
            $name = 'Community Health Worker';
            break;
        case CASEMANAGER:
            $name = 'Case Manager';
            break;
        case MANAGERDIRECTOR:
            $name = 'Medical Director';
            break;
        default:
            $name = '';
            break;
        }
        return $name;
    }
}

if (!function_exists('intervention_action')) {
     
    function intervention_action()
    {
        $common_array = array("Continue Same Plan"=>"Continue Same Plan", "Review Plan"=>"Review Plan");

        return $common_array;
    }
}