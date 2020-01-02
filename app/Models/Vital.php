<?php

namespace App\Models;

use App\Traits\DateFormat;
use Illuminate\Database\Eloquent\Model;

class Vital extends Model
{
    use DateFormat;
    protected $guarded = [];

    const DRAFT = '0';
    const ACTIVE = '1';

    protected $dates_need_to_be_changed = [
        'created_at',
        'updated_at'
    ];
}
