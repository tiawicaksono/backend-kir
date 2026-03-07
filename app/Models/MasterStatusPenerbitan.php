<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterStatusPenerbitan extends Model
{
    protected $primaryKey = 'issuance_id';
    public $incrementing = false;
    protected $keyType = 'int'; // atau string

    protected $fillable = [
        'issuance_id',
        'issuance_code',
        'issuance_name',
        'issuance_desc',
    ];
}
