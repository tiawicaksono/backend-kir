<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterBahanUtama extends Model
{
    use SoftDeletes;

    protected $table = 'master_bahan_utamas';
    protected $fillable = [
        'id',
        'bahan_utama',
    ];

    public function kendaraans()
    {
        return $this->hasMany(MKendaraan::class, 'bahan_utama_id', 'id');
    }
}
