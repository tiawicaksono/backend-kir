<?php

namespace App\Services;

use App\Models\MKendaraan;
use App\Models\TrnHasilUji;
use App\Models\TrnPendaftaran;
use App\Models\TrnPendaftaranRekomendasi;
use App\Models\TrnPendaftaranRetribusi;
use Illuminate\Support\Facades\DB;

class PendaftaranService
{
    // =====================================================
    // FIND LOCAL
    // =====================================================
    public function findLocal($q, $qRaw)
    {
        return MKendaraan::where(function ($query) use ($q, $qRaw) {
            $query->whereRaw("REGEXP_REPLACE(UPPER(no_uji), '[^A-Z0-9]', '', 'g') = ?", [$q])
                ->orWhere('no_mesin', $qRaw)
                ->orWhere('no_rangka', $qRaw)
                ->orWhereRaw("REGEXP_REPLACE(UPPER(no_kendaraan), '[^A-Z0-9]', '', 'g') = ?", [$q]);
        })->first();
    }

    // =====================================================
    // CEK DUPLIKAT HARI INI
    // =====================================================
    public function isAlreadyRegisteredToday($kendaraanId, $issuanceId = null)
    {
        return TrnPendaftaran::where('kendaraan_id', $kendaraanId)
            ->when(
                $issuanceId,
                fn($q) =>
                $q->where('status_penerbitan_id', $issuanceId)
            )
            ->whereDate('created_at', today())
            ->exists();
    }

    // =====================================================
    // DRAFT FROM KEMENHUB
    // =====================================================
    public function createDraftFromKemenhub($data, $status)
    {
        return MKendaraan::create([
            ...$data,
            'status_penerbitan_id' => $status,
        ]);
    }

    // =====================================================
    // RULE CHECKER
    // =====================================================
    private function isHasilUji(int $issuanceId): bool
    {
        return in_array($issuanceId, [1, 2, 7, 8, 9]);
    }

    private function isRekomendasi(int $issuanceId): bool
    {
        return in_array($issuanceId, [5, 6]);
    }

    // =====================================================
    // RETRIBUSI GRATIS
    // =====================================================
    private function createRetribusiGratis(int $pendaftaranId): void
    {
        TrnPendaftaranRetribusi::updateOrCreate(
            ['pendaftaran_id' => $pendaftaranId],
            [
                'b_daftar' => 0,
                'b_cetak' => 0,
                'b_denda' => 0,
                'jumlah_retribusi' => 0,
                'status_pembayaran' => true,
                'virtual_account' => null,
            ]
        );
    }

    // =====================================================
    // GENERATE NOMOR
    // =====================================================
    private function generateNomor(): array
    {
        // HAR IAN
        $lastDaily = TrnPendaftaran::query()
            ->whereDate('created_at', today())
            ->latest('id')
            ->first();

        $dailyNumber = $lastDaily
            ? ((int) $lastDaily->no_pendaftaran_harian + 1)
            : 1;

        $noHarian = str_pad($dailyNumber, 5, '0', STR_PAD_LEFT);

        // TAHUNAN
        $year = now()->year;

        $lastYearly = TrnPendaftaran::query()
            ->whereYear('created_at', $year)
            ->latest('id')
            ->first();

        $yearlyNumber = 1;

        if ($lastYearly?->no_pendaftaran_tahunan) {
            preg_match('/(\d+)$/', $lastYearly->no_pendaftaran_tahunan, $m);
            $yearlyNumber = ((int) ($m[1] ?? 0)) + 1;
        }

        $noTahunan =
            'DISHUB-PKB-' . str_pad($yearlyNumber, 5, '0', STR_PAD_LEFT);

        return [$noHarian, $noTahunan];
    }

    // =====================================================
    // HANDLE KENDARAAN
    // =====================================================
    private function handleKendaraan(array $request, int $issuanceId)
    {
        if ($issuanceId === 1) {

            $kendaraan = MKendaraan::create([
                'no_uji' => now()->format('YmdHis'),
                'no_kendaraan' => $request['no_kendaraan'],
                'no_mesin' => $request['no_mesin'],
                'no_rangka' => $request['no_rangka'],
                'nama_pemilik' => $request['nama_pemilik'],
                'identitas' => $request['identitas'],
                'no_identitas' => $request['no_identitas'],
                'alamat' => $request['alamat'],
                'no_hp' => $request['no_hp'],
                'provinsi_id' => $request['provinsi_id'],
                'kota_id' => $request['kota_id'],
                'kecamatan_id' => $request['kecamatan_id'],
                'kelurahan_id' => $request['kelurahan_id'],
            ]);

            return $kendaraan->id;
        }

        $kendaraan = MKendaraan::findOrFail($request['kendaraan_id']);

        $kendaraan->update([
            'no_kendaraan' => $request['no_kendaraan'],
            'no_mesin' => $request['no_mesin'],
            'no_rangka' => $request['no_rangka'],
            'nama_pemilik' => $request['nama_pemilik'],
            'identitas' => $request['identitas'],
            'no_identitas' => $request['no_identitas'],
            'alamat' => $request['alamat'],
            'no_hp' => $request['no_hp'],
            'provinsi_id' => $request['provinsi_id'],
            'kota_id' => $request['kota_id'],
            'kecamatan_id' => $request['kecamatan_id'],
            'kelurahan_id' => $request['kelurahan_id'],
        ]);

        return $kendaraan->id;
    }

    // =====================================================
    // STORE PENDAFTARAN
    // =====================================================
    public function storePendaftaran(array $request, $user)
    {
        return DB::transaction(function () use ($request, $user) {

            $issuanceId = (int) $request['status_penerbitan_id'];

            if ($this->isAlreadyRegisteredToday($request['kendaraan_id'], $issuanceId)) {
                throw new \Exception("Kendaraan sudah terdaftar hari ini");
            }

            [$noHarian, $noTahunan] = $this->generateNomor();

            $kendaraanId = $this->handleKendaraan($request, $issuanceId);

            // =====================================================
            // 🔥 PENGURUS RESOLVER (DI SINI TEMPATNYA)
            // =====================================================
            $pengurus = null;

            if (!empty($request['biro_jasa_id'])) {

                $biroJasa = \App\Models\MBiroJasa::find($request['biro_jasa_id']);

                $pengurus = [
                    'nama_pengurus' => $biroJasa?->nama_pengurus,
                    'company_pengurus' => $biroJasa?->company_pengurus,
                    'no_hp_pengurus' => $biroJasa?->no_hp_pengurus,
                ];
            }

            // =====================================================
            // PENDAFTARAN
            // =====================================================
            $pendaftaran = TrnPendaftaran::create([

                'kendaraan_id' => $kendaraanId,
                'petugas_id' => $user->id,
                'petugas_nama' => $user->name,
                'status_penerbitan_id' => $issuanceId,

                'no_pendaftaran_harian' => $noHarian,
                'no_pendaftaran_tahunan' => $noTahunan,

                'tanggal_pendaftaran' => now(),
                'tanggal_uji' => $request['tanggal_uji'],
                'tanggal_mati_uji' => $request['tanggal_mati_uji'],

                'is_dikuasakan' => $request['is_dikuasakan'] ?? false,
                'biro_jasa_id' => $request['biro_jasa_id'],

                // =================================================
                // 👇 PRIORITAS: biro jasa → fallback manual
                // =================================================
                'nama_pengurus' => $pengurus['nama_pengurus']
                    ?? $request['nama_pengurus']
                    ?? null,

                'company_pengurus' => $pengurus['company_pengurus']
                    ?? $request['company_pengurus']
                    ?? null,

                'no_hp_pengurus' => $pengurus['no_hp_pengurus']
                    ?? $request['no_hp_pengurus']
                    ?? null,

                'is_kartu_hilang' => $issuanceId === 4,
                'no_kartu_hilang' => $request['no_kartu_hilang'],

                'status' => true,
            ]);

            // =====================================================
            // RETRIBUSI GRATIS SELAMANYA
            // =====================================================
            $this->createRetribusiGratis($pendaftaran->id);

            // =====================================================
            // HASIL UJI
            // =====================================================
            if ($this->isHasilUji($issuanceId)) {
                TrnHasilUji::create([
                    'pendaftaran_id' => $pendaftaran->id
                ]);
            }

            // =====================================================
            // REKOMENDASI
            // =====================================================
            if ($this->isRekomendasi($issuanceId)) {
                TrnPendaftaranRekomendasi::create([
                    'pendaftaran_id' => $pendaftaran->id,
                    'is_mutasi_keluar' => $issuanceId === 6,
                    'is_numpang_keluar' => $issuanceId === 5,
                ]);
            }

            return $pendaftaran->fresh([
                'kendaraan',
                'statusPenerbitan',
                'petugas'
            ]);
        });
    }

    // =====================================================
    // UPDATE PENDAFTARAN
    // =====================================================
    public function updatePendaftaran(TrnPendaftaran $pendaftaran, array $data)
    {
        return DB::transaction(function () use ($pendaftaran, $data) {

            $issuanceId = (int) $data['status_penerbitan_id'];
            $tanggalUji = $data['tanggal_uji'];

            // =====================================================
            // GUARD DUPLIKAT
            // =====================================================
            $exists = TrnPendaftaran::query()
                ->where('id', '!=', $pendaftaran->id)
                ->where('kendaraan_id', $pendaftaran->kendaraan_id)
                ->where('status_penerbitan_id', $issuanceId)
                ->whereDate('tanggal_uji', $tanggalUji)
                ->exists();

            if ($exists) {
                throw new \Exception(
                    'Data dengan jenis pendaftaran dan tanggal uji tersebut sudah ada'
                );
            }

            // =====================================================
            // VALIDASI BOLEH SWITCH STATE?
            // =====================================================

            $oldIssuance = (int) $pendaftaran->status_penerbitan_id;

            $oldIsHasil = $this->isHasilUji($oldIssuance);
            $newIsHasil = $this->isHasilUji($issuanceId);

            $oldIsRekom = $this->isRekomendasi($oldIssuance);
            $newIsRekom = $this->isRekomendasi($issuanceId);

            $isSwitchingState =
                ($oldIsHasil && $newIsRekom) ||
                ($oldIsRekom && $newIsHasil);

            if ($isSwitchingState) {

                $check = $this->canDeletePendaftaran($pendaftaran->id);

                if (!$check['allowed']) {
                    throw new \Exception($check['message']);
                }
            }

            // =====================================================
            // UPDATE PENDAFTARAN
            // =====================================================
            $pendaftaran->update([
                'status_penerbitan_id' => $issuanceId,
                'tanggal_uji' => $tanggalUji,
            ]);

            // =====================================================
            // RETRIBUSI GRATIS
            // =====================================================
            $this->createRetribusiGratis($pendaftaran->id);

            // =====================================================
            // HASIL UJI
            // =====================================================
            if ($newIsHasil) {

                // hapus rekomendasi
                TrnPendaftaranRekomendasi::where(
                    'pendaftaran_id',
                    $pendaftaran->id
                )->delete();

                TrnHasilUji::updateOrCreate(
                    [
                        'pendaftaran_id' => $pendaftaran->id
                    ],
                );
            }

            // =====================================================
            // REKOMENDASI
            // =====================================================
            if ($newIsRekom) {

                // hapus hasil uji
                TrnHasilUji::where(
                    'pendaftaran_id',
                    $pendaftaran->id
                )->delete();

                TrnPendaftaranRekomendasi::updateOrCreate(
                    [
                        'pendaftaran_id' => $pendaftaran->id
                    ],
                    [
                        'is_mutasi_keluar' => $issuanceId === 6,
                        'is_numpang_keluar' => $issuanceId === 5,
                    ]
                );
            }

            return $pendaftaran->fresh([
                'kendaraan',
                'statusPenerbitan',
                'petugas'
            ]);
        });
    }

    // =====================================================
    // CAN DELETE PENDAFTARAN
    // =====================================================
    public function canDeletePendaftaran(int $pendaftaranId): array
    {
        // =====================================================
        // RULE 1: HASIL UJI - is_datang = true
        // =====================================================
        $hasDatang = TrnHasilUji::where('pendaftaran_id', $pendaftaranId)
            ->where('is_datang', true)
            ->exists();

        if ($hasDatang) {
            return [
                'allowed' => false,
                'message' => 'Forbidden! Kendaraan sudah datang uji',
            ];
        }

        // =====================================================
        // RULE 2: REKOMENDASI - sudah sinkron
        // =====================================================
        $hasSinkron = TrnPendaftaranRekomendasi::where('pendaftaran_id', $pendaftaranId)
            ->whereNotNull('keterangan_sinkron')
            ->where('keterangan_sinkron', '!=', '')
            ->exists();

        if ($hasSinkron) {
            return [
                'allowed' => false,
                'message' => 'Forbidden! Data rekomendasi sudah tersinkron',
            ];
        }

        return [
            'allowed' => true,
            'message' => null,
        ];
    }
}
