<?php

namespace App\Http\Controllers;

use App\Helpers\FlattenHelper;
use App\Models\MKendaraan;
use App\Models\TrnPendaftaran;
use App\Models\TrnPendaftaranRekomendasi;
use App\Models\TrnPendaftaranRetribusi;
use App\Services\KemenhubService;
use App\Services\PendaftaranService;
use App\Services\QueryFilterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        DB::beginTransaction();
        $user = $request->user();
        try {

            // =====================================================
            // ISSUANCE
            // =====================================================

            $issuanceId = (int) $request->status_penerbitan_id;

            // =====================================================
            // CEK DUPLIKAT HARI INI
            // =====================================================

            $existing = TrnPendaftaran::query()
                ->where('kendaraan_id', $request->kendaraan_id)
                ->where('status_penerbitan_id', $issuanceId)
                ->whereDate('created_at', today())
                ->first();

            if ($existing) {

                return response()->json([
                    'success' => false,
                    'message' => 'Kendaraan sudah terdaftar hari ini',
                ], 422);
            }

            // =====================================================
            // GENERATE NOMOR HARIAN
            // reset setiap hari
            // =====================================================

            $lastDaily = TrnPendaftaran::query()
                ->whereDate('created_at', today())
                ->latest('id')
                ->first();

            $dailyNumber = 1;

            if ($lastDaily?->no_pendaftaran_harian) {

                $dailyNumber =
                    ((int) $lastDaily->no_pendaftaran_harian) + 1;
            }

            $noPendaftaranHarian = str_pad(
                $dailyNumber,
                5,
                '0',
                STR_PAD_LEFT
            );

            // =====================================================
            // GENERATE NOMOR TAHUNAN
            // reset setiap tahun
            // =====================================================

            $currentYear = now()->year;

            $lastYearly = TrnPendaftaran::query()
                ->whereYear('created_at', $currentYear)
                ->latest('id')
                ->first();

            $yearlyNumber = 1;

            if ($lastYearly?->no_pendaftaran_tahunan) {

                preg_match(
                    '/(\d+)$/',
                    $lastYearly->no_pendaftaran_tahunan,
                    $matches
                );

                $yearlyNumber =
                    ((int) ($matches[1] ?? 0)) + 1;
            }

            $noPendaftaranTahunan =
                'DISHUB-PKB-' .
                str_pad(
                    $yearlyNumber,
                    5,
                    '0',
                    STR_PAD_LEFT
                );

            // =====================================================
            // KENDARAAN
            // =====================================================

            $kendaraanId = $request->kendaraan_id;

            // =====================================================
            // DAFTAR BARU
            // insert kendaraan
            // =====================================================

            if ($issuanceId === 1) {

                $kendaraan = MKendaraan::create([

                    'no_uji' => now()->format('YmdHis'),

                    'no_kendaraan' => $request->no_kendaraan,
                    'no_mesin' => $request->no_mesin,
                    'no_rangka' => $request->no_rangka,

                    'nama_pemilik' => $request->nama_pemilik,
                    'identitas' => $request->identitas,
                    'no_identitas' => $request->no_identitas,
                    'alamat' => $request->alamat,
                    'no_hp' => $request->no_hp,

                    'provinsi_id' => $request->provinsi_id,
                    'kota_id' => $request->kota_id,
                    'kecamatan_id' => $request->kecamatan_id,
                    'kelurahan_id' => $request->kelurahan_id,
                ]);

                $kendaraanId = $kendaraan->id;
            }

            // =====================================================
            // SELAIN DAFTAR BARU
            // update kendaraan
            // =====================================================

            else {

                $kendaraan = MKendaraan::findOrFail($kendaraanId);

                $kendaraan->update([

                    'no_kendaraan' => $request->no_kendaraan,
                    'no_mesin' => $request->no_mesin,
                    'no_rangka' => $request->no_rangka,

                    'nama_pemilik' => $request->nama_pemilik,
                    'identitas' => $request->identitas,
                    'no_identitas' => $request->no_identitas,
                    'alamat' => $request->alamat,
                    'no_hp' => $request->no_hp,

                    'provinsi_id' => $request->provinsi_id,
                    'kota_id' => $request->kota_id,
                    'kecamatan_id' => $request->kecamatan_id,
                    'kelurahan_id' => $request->kelurahan_id,
                ]);
            }

            // =====================================================
            // PENDAFTARAN
            // =====================================================

            $pendaftaran = TrnPendaftaran::create([

                'kendaraan_id' => $kendaraanId,
                'petugas_id' => $user->id,
                'petugas_nama' => $user->name,
                'status_penerbitan_id' => $issuanceId,

                'no_pendaftaran_harian' => $noPendaftaranHarian,

                'no_pendaftaran_tahunan' => $noPendaftaranTahunan,

                'tanggal_pendaftaran' => now(),

                'tanggal_uji' => $request->tanggal_uji,

                'tanggal_mati_uji' => $request->tanggal_mati_uji,

                'is_dikuasakan' => $request->is_dikuasakan ?? false,

                'biro_jasa_id' => $request->biro_jasa_id,

                'nama_pengurus' => $request->nama_pengurus,

                'company_pengurus' => $request->company_pengurus,

                'no_hp_pengurus' => $request->no_hp_pengurus,

                'is_kartu_hilang' => $issuanceId === 4,

                'no_kartu_hilang' => $request->no_kartu_hilang,

                'status' => true,
            ]);

            // =====================================================
            // RETRIBUSI
            // =====================================================

            TrnPendaftaranRetribusi::create([

                'pendaftaran_id' => $pendaftaran->id,

                'b_daftar' => 0,

                'b_cetak' => 0,

                'b_denda' => 0,

                'jumlah_retribusi' => 0,

                'status_pembayaran' => true,

                'virtual_account' => null,
            ]);

            // =====================================================
            // NUMPANG / MUTASI KELUAR
            // =====================================================

            if (in_array($issuanceId, [5, 6])) {

                TrnPendaftaranRekomendasi::create([

                    'pendaftaran_id' => $pendaftaran->id,

                    'is_mutasi_keluar' => $issuanceId === 6,

                    'is_numpang_keluar' => $issuanceId === 5,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil simpan',
                'data' => $pendaftaran,
            ]);
        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function index(Request $request)
    {
        $model = TrnPendaftaran::class;

        $query = $model::with([
            'kendaraan:id,no_uji,no_kendaraan,nama_pemilik',
            'statusPenerbitan:issuance_id,issuance_name',
        ]);

        $config = $this->getTableConfig();

        // =========================================
        // DEFAULT DATE = TODAY
        // =========================================

        $date = $request->date ?? today()->toDateString();

        $query->whereDate('tanggal_pendaftaran', $date);

        // =========================================
        // DEFAULT SORT
        // =========================================

        $primaryKey = $config['primary_key'] ?? null;

        if (!$request->filled('sort_by') && $primaryKey) {

            $request->merge([
                'sort_by' => $primaryKey,
                'sort_order' => 'desc',
            ]);
        }

        QueryFilterService::apply(
            $query,
            $request,
            $model,
            $config
        );

        $perPage = $request->limit ?? 10;
        $result = $query->paginate($perPage);

        // =========================================
        // FLATTEN
        // =========================================

        $data = FlattenHelper::flatten(
            $result->items(),
            $config
        );

        return response()->json([
            'data' => $data,

            'meta' => [
                'current_page' => $result->currentPage(),
                'per_page' => $result->perPage(),
                'total' => $result->total(),
            ],

            'config' => $config,
        ]);
    }

    private function getTableConfig()
    {
        return [

            'primary_key' => 'id',
            'only_fields' => [
                'id',
                'no_pendaftaran_harian',
                'tanggal_uji',
            ],
            'only' => [
                'kendaraan' => [
                    'only' => [
                        'no_uji',
                        'no_kendaraan',
                        'nama_pemilik',
                    ],
                ],

                'statusPenerbitan' => [
                    'only' => [
                        'issuance_name',
                    ],
                ]
            ],

            'labels' => [
                'no_pendaftaran_harian' => 'No Antrian',
                'tanggal_uji' => 'Tanggal Uji',

                'kendaraan_no_uji' => 'No Uji',
                'kendaraan_no_kendaraan' => 'No Kendaraan',
                'kendaraan_nama_pemilik' => 'Nama Pemilik',

                'status_penerbitan_issuance_name' => 'Pendaftaran',
            ],

            /**
             * 🔥 WAJIB DOT NOTATION
             */
            'searchable' => [
                [
                    'field' => 'no_pendaftaran_harian',
                    'label' => 'No Antrian',
                ],
                [
                    'field' => 'kendaraan.no_uji',
                    'label' => 'No Uji',
                ],
                [
                    'field' => 'kendaraan.no_kendaraan',
                    'label' => 'No Kendaraan',
                ],
                [
                    'field' => 'kendaraan.nama_pemilik',
                    'label' => 'Nama Pemilik',
                ],
                [
                    'field' => 'statusPenerbitan.issuance_name',
                    'label' => 'Status Penerbitan',
                ],

            ],

            'sortable' => [
                'id',
                'no_pendaftaran_harian'
            ],

            'hidden' => [
                'status_penerbitan_issuance_id',
            ],
        ];
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
