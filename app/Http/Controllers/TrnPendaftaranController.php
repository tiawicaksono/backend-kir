<?php

namespace App\Http\Controllers;

use App\Models\TrnPendaftaran;
use App\Services\KemenhubService;
use App\Services\PendaftaranService;
use Illuminate\Http\Request;

class TrnPendaftaranController extends Controller
{

    public function search(Request $request, KemenhubService $kemenhub, PendaftaranService $pendaftaranService)
    {
        $qRaw = $request->q;

        $q = strtoupper(preg_replace('/[^A-Z0-9]/', '', $qRaw));
        $status = (int) $request->status_penerbitan_id;

        // =========================
        // 🚀 KHUSUS 7 / 8
        // =========================
        if (in_array($status, [7, 8])) {

            $statusKeluar = $status === 7 ? 5 : 6;

            $dataKemenhub = $kemenhub->checkPengujianKeluar($q, $statusKeluar);

            // ❌ tidak ada di pusat
            if (!$dataKemenhub) {
                return response()->json([
                    'source' => 'none',
                    'found' => false
                ]);
            }

            // 🔍 cek lokal
            $kendaraan = $pendaftaranService->findLocal($q, $qRaw);

            // =========================
            // ✅ kalau lokal ada
            // =========================
            if ($kendaraan) {

                // 🚫 cek sudah daftar hari ini
                if ($pendaftaranService->isAlreadyRegisteredToday($kendaraan->id)) {
                    return response()->json([
                        'source' => 'local',
                        'found' => true,
                        'blocked_today' => true,
                        'message' => 'Kendaraan sudah terdaftar hari ini',
                        'data' => $kendaraan
                    ]);
                }

                return response()->json([
                    'source' => 'local',
                    'found' => true,
                    'data' => $kendaraan
                ]);
            }

            // =========================
            // 🔥 kalau tidak ada lokal → auto draft
            // =========================
            // $mapped = $kemenhub->mapToKendaraan($dataKemenhub);

            // $kendaraan = $pendaftaranService->createDraftFromKemenhub($mapped, $status);

            $kendaraan = $kemenhub->mapToKendaraan($dataKemenhub);

            return response()->json([
                'source' => 'kementrian',
                'found' => true,
                'data' => $kendaraan
            ]);
        }

        // =========================
        // 🟢 SELAIN 7 / 8
        // =========================
        $kendaraan = $pendaftaranService->findLocal($q, $qRaw);

        if ($kendaraan) {

            if ($pendaftaranService->isAlreadyRegisteredToday($kendaraan->id)) {
                return response()->json([
                    'source' => 'local',
                    'found' => true,
                    'blocked_today' => true,
                    'message' => 'Kendaraan sudah terdaftar hari ini',
                    'data' => $kendaraan
                ]);
            }

            return response()->json([
                'source' => 'local',
                'found' => true,
                'data' => $kendaraan
            ]);
        }

        return response()->json([
            'source' => 'none',
            'found' => false
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(TrnPendaftaran $tblPendaftaran)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TrnPendaftaran $tblPendaftaran)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TrnPendaftaran $tblPendaftaran)
    {
        //
    }
}
