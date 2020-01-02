<?php

namespace App\Traits;

use App\Models\Registry;


trait RegistryTrait
{
    public function query() {
        $query = Registry::query()->orderBy('id', 'DESC');
        
        //only return records according to coming request type
        $query->where('type', request()->type);
        return $query;
    }

    //this function will decide the listing table columns and their titles
    public function getColumns() {
        $return_value['DT_RowIndex'] = ['title' => 'Sr. No', 'orderable' => false, 'searchable' => false];
        
        (request()->type != 'referral_sources' && request()->type != 'rehabs' && request()->type != 'hospice_providers' && request()->type != 'housing_assistances' && request()->type != 'mental_health_assistances' && request()->type != 'home_health_providers' && request()->type != 'insurances') ? $return_value['name'] = ['title' => (request()->type == 'pcp_informations' || request()->type == 'specialities')?'Doctor Name':'Name', 'orderable' => false]:'';

        
        (request()->type !='emergency_departments') ?  $return_value['org_name'] = ['title' => 'Organization', 'orderable' => false] :'';
        

        (request()->type != 'rehabs' && request()->type != 'hospice_providers' && request()->type != 'housing_assistances' && request()->type != 'mental_health_assistances' && request()->type != 'home_health_providers' && request()->type != 'insurances') ? $return_value['email'] = ['title' => 'Email', 'orderable' => false]:'';        

        (request()->type == 'insurances') ? $return_value['contact_email'] = ['title' => 'Contact Person Email', 'orderable' => false]:'';
        
        $return_value['contact_name'] = ['title' => (request()->type == 'housing_assistances' || request()->type == 'mental_health_assistances' || request()->type == 'home_health_providers') ? 'Counselor Name':'Contact Person Name', 'orderable' => false];
        
        (request()->type == 'rehabs' || request()->type == 'hospice_providers' || request()->type == 'housing_assistances' || request()->type == 'mental_health_assistances' || request()->type == 'home_health_providers') ? $return_value['contact_title'] = ['title' => (request()->type == 'housing_assistances' || request()->type == 'mental_health_assistances' || request()->type == 'home_health_providers') ? 'Counselor Title':'Contact Title', 'orderable' => false]:'';
        
        $return_value['city'] = ['title' => 'Address', 'orderable' => false];
        
            
        (request()->type == 'referral_sources') ? $return_value['web_address'] = ['title' => 'Web Address', 'orderable' => false]:'';
        
        (request()->type != 'pcp_informations' && request()->type != 'specialities' && request()->type != 'rehabs' && request()->type != 'hospice_providers' && request()->type != 'housing_assistances' && request()->type != 'mental_health_assistances' && request()->type != 'home_health_providers')?$return_value['code'] = ['title' => 'Code', 'orderable' => false]:'';

        (request()->type == 'pcp_informations' || request()->type == 'specialities')?$return_value['speciality'] = ['title' => 'Specialty', 'orderable' => false]:'';

        $return_value['action'] = ['title' => 'Action', 'orderable' => false];
        
        return $return_value;
    }
}