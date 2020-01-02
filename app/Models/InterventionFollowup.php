<?php

namespace App\Models;

use App\Traits\DateFormat;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;


class InterventionFollowup extends Model implements Auditable
{
    //

     /* For Audit log*/
    use \OwenIt\Auditing\Auditable;
    use DateFormat;
    protected $guarded = [];

    const DRAFT = '0';
    const ACTIVE = '1';
    const COMPLETE = '1';
    const INCOMPLETE = '2';

    const TYPE_ASSESSMENT = 0;
    const TYPE_CHECKPOINT = 1;

    protected $dates_need_to_be_changed = [
        'created_at',
        'updated_at',
        'follow_up_date',
        'added_date',
    ];
}
