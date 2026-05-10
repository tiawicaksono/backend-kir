<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrnPendaftaranRetribusi extends Model
{
    protected $table = 'trn_pendaftaran_retribusis';
    protected $primaryKey = 'pendaftaran_id';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'pendaftaran_id',
        'b_daftar',
        'b_cetak',
        'b_denda',
        'jumlah_retribusi',
        'status_pembayaran',
        'virtual_account'
    ];

    protected $casts = [
        'b_daftar' => 'integer',
        'b_cetak' => 'integer',
        'b_denda' => 'integer',
        'jumlah_retribusi' => 'integer',
        'status_pembayaran' => 'boolean',
        'virtual_account' => 'string',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    // 🔗 ke pendaftaran
    public function pendaftaran()
    {
        return $this->belongsTo(TrnPendaftaran::class, 'pendaftaran_id');
    }
}
