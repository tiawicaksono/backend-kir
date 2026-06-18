<?php

namespace App\Http\Controllers;

use App\Helpers\FlattenHelper;
use App\Models\MKuota;
use App\Models\TrnPendaftaran;
use App\Services\KemenhubService;
use App\Services\PendaftaranService;
use App\Services\QueryFilterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrnPendaftaranController extends Controller
{
    public function __construct(
        private PendaftaranService $pendaftaranService
    ) {}

    // =====================================================
    // SEARCH
    // =====================================================
    public function search(
        Request $request,
        KemenhubService $kemenhub
    ) {
        $qRaw = $request->q;

        $q = strtoupper(preg_replace('/[^A-Z0-9]/', '', $qRaw));
        $status = (int) $request->status_penerbitan_id;

        // =========================
        // KHUSUS 7 / 8
        // =========================
        if (in_array($status, [7, 8])) {

            $statusKeluar = $status === 7 ? 5 : 6;

            $dataKemenhub = $kemenhub
                ->checkPengujianKeluar($q, $statusKeluar);

            // ❌ tidak ada di pusat
            if (!$dataKemenhub) {
                return response()->json([
                    'source' => 'none',
                    'found' => false
                ]);
            }

            // 🔍 cek lokal
            $kendaraan = $this->pendaftaranService
                ->findLocal($q, $qRaw);

            // =========================
            // ✅ kalau lokal ada
            // =========================
            if ($kendaraan) {

                // 🚫 cek sudah daftar hari ini
                if ($this->pendaftaranService
                    ->isAlreadyRegisteredToday($kendaraan->id)
                ) {
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
                'source' => 'kementrian',
                'found' => true,
                'data' => $kemenhub->mapToKendaraan($dataKemenhub)
            ]);
        }

        // =========================
        // NORMAL SEARCH
        // =========================
        $kendaraan = $this->pendaftaranService
            ->findLocal($q, $qRaw);

        if ($kendaraan) {

            if ($this->pendaftaranService
                ->isAlreadyRegisteredToday($kendaraan->id)
            ) {
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

    // =====================================================
    // STORE
    // =====================================================
    public function store(Request $request)
    {
        try {

            $pendaftaran = DB::transaction(function () use ($request) {

                $tanggalUji = $request->tanggal_uji;

                // Lock per tanggal uji
                DB::select(
                    "SELECT pg_advisory_xact_lock(hashtext(?))",
                    [$tanggalUji]
                );

                $kuota = MKuota::value('kuota');

                if (!$kuota) {
                    throw new \Exception('Kuota tanggal uji belum diatur');
                }

                $totalPendaftar = TrnPendaftaran::whereDate('tanggal_uji', $tanggalUji)
                    ->whereIn('status_penerbitan_id', [1, 2, 7, 8, 9])
                    ->count();

                if ($totalPendaftar >= $kuota) {
                    throw new \Exception('Kuota tanggal uji sudah penuh');
                }

                return $this->pendaftaranService->storePendaftaran(
                    $request->all(),
                    $request->user()
                );
            });

            $data = [
                'id' => $pendaftaran->id,
                'no_pendaftaran_harian' => $pendaftaran->no_pendaftaran_harian,
                'tanggal_uji' => $pendaftaran->tanggal_uji,
                'kendaraan_no_uji' => $pendaftaran->kendaraan?->no_uji,
                'kendaraan_no_kendaraan' => $pendaftaran->kendaraan?->no_kendaraan,
                'kendaraan_nama_pemilik' => $pendaftaran->kendaraan?->nama_pemilik,
                'status_penerbitan_issuance_id' => $pendaftaran->statusPenerbitan?->issuance_id,
                'status_penerbitan_issuance_name' => $pendaftaran->statusPenerbitan?->issuance_name,
                'petugas_name' => $pendaftaran->petugas?->name,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Berhasil simpan',
                'data' => $data,
            ]);
        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    // =====================================================
    // INDEX
    // =====================================================
    public function index(Request $request)
    {
        $model = TrnPendaftaran::class;
        $user = $request->user();

        $query = $model::with([
            'kendaraan:id,no_uji,no_kendaraan,nama_pemilik',
            'statusPenerbitan:issuance_id,issuance_name',
            'petugas:id,name',
        ]);

        $config = $this->getTableConfig();

        // =========================================
        // DEFAULT BY CREATED
        // =========================================
        if (!$user->hasRole(1)) {
            $query->where('petugas_id', $user->id);
        }
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
                'sort_by' => $config['primary_key'],
                'sort_order' => 'desc',
            ]);
        }

        QueryFilterService::apply(
            $query,
            $request,
            $model,
            $config
        );

        $result = $query->paginate($request->limit ?? 10);

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

    // =====================================================
    // UPDATE
    // =====================================================
    public function update(Request $request, TrnPendaftaran $pendaftaran)
    {
        try {

            $updated = $this->pendaftaranService
                ->updatePendaftaran(
                    $pendaftaran,
                    $request->all()
                );

            $data = [
                'id' => $updated->id,
                'status_penerbitan_issuance_id' =>
                $updated->statusPenerbitan?->issuance_id,
                'tanggal_uji' => $updated->tanggal_uji,
                'status_penerbitan_issuance_name' =>
                $updated->statusPenerbitan?->issuance_name,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Berhasil update',
                'data' => $data,
            ]);
        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // =====================================================
    // DESTROY
    // =====================================================
    public function destroy(TrnPendaftaran $pendaftaran)
    {
        DB::beginTransaction();

        try {

            $check = $this->pendaftaranService
                ->canDeletePendaftaran($pendaftaran->id);

            if (!$check['allowed']) {

                return response()->json([
                    'success' => false,
                    'message' => $check['message'],
                ], 422);
            }

            $pendaftaran->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil hapus',
            ]);
        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // =====================================================
    // CONFIG
    // =====================================================
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
                        'issuance_id',
                        'issuance_name',
                    ],
                ],
                'petugas' => [
                    'only' => [
                        'name',
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
                'petugas_name' => 'Petugas',
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
                [
                    'field' => 'petugas.name',
                    'label' => 'Petugas',
                ]
            ],

            'sortable' => [
                'id',
                'no_pendaftaran_harian'
            ],
        ];
    }
}
