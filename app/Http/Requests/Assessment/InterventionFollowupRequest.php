<?php

namespace App\Http\Requests\Assessment;

use Illuminate\Foundation\Http\FormRequest;

class InterventionFollowupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if(!$_COOKIE['client_timezone']){
            $timezone=Config::get('app.timezone');
        }
        else{
            $timezone=$_COOKIE['client_timezone'];
        }
        $yesterdayDate = \Carbon\Carbon::now()->subDay()->timezone($timezone)->format('m-d-Y');
        return [
            'follow_up_item' => 'required|max:200',
            'follow_up_date' => "required|date_format:m-d-Y|after:".$yesterdayDate,
        ];
    }



}
