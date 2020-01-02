<?php

namespace App\Models\Admin\CarePlan;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContentDiscussed extends Model
{
    use SoftDeletes;

    const ACTIVE = '1';

    const CONTENT_PREFIX = 'CID';

    protected $table = 'content_discuss';

    protected $dates = ['deleted_at'];

    protected $guarded = ['id','created_at','deleted_at','updated_at'];


    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [self::INACTIVE])->orderBy('created_at','desc');
    }
}
