<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrnSinkron extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'name',
        'status',
        'keterangan',
        'created_at',
    ];
}
