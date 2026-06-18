<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MKuota extends Model
{
    protected $table = 'm_kuotas';

    protected $fillable = [
        'kuota',
    ];
}
