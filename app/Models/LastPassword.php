<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LastPassword extends Model
{
    public $timestamps = true;

    protected $guarded = ['id','created_at','deleted_at','updated_at'];
}
