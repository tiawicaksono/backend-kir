<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrnPendaftaranKartuUji extends Model
{
    protected $table = 'trn_pendaftaran_kartu_ujis';
    protected $primaryKey = 'pendaftaran_id';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'pendaftaran_id',
        'no_seri_kartu',
        'tanggal_cetak',
        'petugas_id',
        'petugas_nama'
    ];

    protected $casts = [
        'no_seri_kartu' => 'string',
        'tanggal_cetak' => 'date',
        'petugas_id' => 'int',
        'petugas_nama' => 'string'
    ];

    public function pendaftaran()
    {
        return $this->belongsTo(TrnPendaftaran::class, 'pendaftaran_id');
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }
}
