<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterKonfigurasiSumbu extends Model
{
    use SoftDeletes;

    protected $table = 'master_konfigurasi_sumbus';
    protected $fillable = [
        'name'
    ];

    public function kendaraan()
    {
        return $this->hasMany(MKendaraan::class, 'konfigurasi_sumbu_id', 'id');
    }
}
