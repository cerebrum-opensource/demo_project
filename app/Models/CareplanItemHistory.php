<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CareplanItemHistory extends Model
{
    protected $guarded = [];

    const TYPE_MEDICATION = 0;
    const TYPE_ALLERGY = 1;

    protected $casts = [
        'form_data' => 'json',
    ];


}
