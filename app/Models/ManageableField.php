<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

class ManageableField extends Model
{
    //

    use SoftDeletes;
    //use Cachable;

    protected $guarded = ['id','created_at','deleted_at','updated_at'];

    protected $dates = ['deleted_at'];




    public function getTypeValueAttribute($value)
    {
    	$value=$this->attributes['type'];
		switch ($value) {
        case 'patient_concern':
            $status = 'Patient Concern';
            break;
        case 'lives_with':
            $status = 'Lives With';
            break;
        case 'document_category':
            $status = 'Document Category';
            break;
        case 'county':
            $status = 'County';
            break;
        case 'language':
            $status = 'Language';
            break;
        case 'flag':
            $status = 'Flag';
            break;
        case 'metric':
            $status = 'Metric';
            break;
        case 'patient_functioning':
            $status = 'Patient Functioning';
            break;
        case 'durable_medical_equipment':
            $status = 'Durable Medical Equipment';
            break;
        case 'identifying_issues':
            $status = 'Identifying Issues';
            break;    
        default:
            $status = '';
            break;
        }
        return $status;
    }

    public function getValueArray()
    {
        $values = preg_replace('~[[\]]~', '', $this->value);
        return explode(',',$values);
    }
}
