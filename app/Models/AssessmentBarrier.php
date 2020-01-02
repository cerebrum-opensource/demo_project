<?php

namespace App\Models;

use App\Models\Admin\CarePlan\Barrier;
use App\Traits\DateFormat;
use Illuminate\Database\Eloquent\Model;

class AssessmentBarrier extends Model
{
    use DateFormat;
    protected $guarded = [];

    const DRAFT = '0';
    const ACTIVE = '1';

    protected $dates_need_to_be_changed = [
        'created_at',
        'updated_at'
    ];

    public function barrier()
    {
        return $this->hasOne(Barrier::class, 'id', 'barrier_id');
    }

}
