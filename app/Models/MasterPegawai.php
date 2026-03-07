<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterPegawai extends Model
{
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'int'; // atau string

    protected $fillable = [
        'job_type_id',
        'job_type_code',
        'job_type_name',
        'job_id',
        'job_code',
        'job_name',
        'user_id',
        'identity_number',
        'full_name',
        'pangkat',
        'email',
        'phone_number',
        'address',
        'sign_active',
        'sign1',
        'sign2',
        'sign3',
        'job_active',
    ];
}
