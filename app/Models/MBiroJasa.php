<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MBiroJasa extends Model
{
    protected $table = 'm_biro_jasas';

    protected $fillable = [
        'name',
        'company',
        'no_hp',
        'status'
    ];

    public function pendaftarans()
    {
        return $this->hasMany(TrnPendaftaran::class, 'biro_jasa_id', 'id');
    }
}
