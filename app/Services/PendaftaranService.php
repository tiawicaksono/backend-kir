<?php

namespace App\Services;

use App\Models\MKendaraan;
use App\Models\TrnPendaftaran;

class PendaftaranService
{
    public function findLocal($q, $qRaw)
    {
        return MKendaraan::where(function ($query) use ($q, $qRaw) {
            $query->whereRaw("REGEXP_REPLACE(UPPER(no_uji), '[^A-Z0-9]', '', 'g') = ?", [$q])
                ->orWhere('no_mesin', $qRaw)
                ->orWhere('no_rangka', $qRaw)
                ->orWhereRaw("REGEXP_REPLACE(UPPER(no_kendaraan), '[^A-Z0-9]', '', 'g') = ?", [$q]);
        })->first();
    }

    public function isAlreadyRegisteredToday($kendaraanId)
    {
        return TrnPendaftaran::where('kendaraan_id', $kendaraanId)
            ->whereDate('created_at', now()->toDateString())
            ->exists();
    }

    public function createDraftFromKemenhub($data, $status)
    {
        return MKendaraan::create([
            ...$data,
            'status_penerbitan_id' => $status,
        ]);
    }
}
