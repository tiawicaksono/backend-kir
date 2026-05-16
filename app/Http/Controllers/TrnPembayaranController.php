<?php

namespace App\Http\Controllers;

use App\Helpers\FlattenHelper;
use App\Models\TrnPendaftaran;
use App\Models\TrnPendaftaranRetribusi;
use App\Services\QueryFilterService;
use Illuminate\Http\Request;

class TrnPembayaranController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function index(Request $request)
    {
        $model = TrnPendaftaran::class;
        $user = $request->user();

        $query = $model::with([
            'kendaraan:id,no_uji,no_kendaraan,nama_pemilik',
            'statusPenerbitan:issuance_id,issuance_name',
            'petugas:id,name',
            'retribusi:pendaftaran_id,b_daftar,b_cetak,b_denda,jumlah_retribusi,status_pembayaran,virtual_account',
        ]);

        $config = $this->getTableConfig();

        // ROLE FILTER
        if (!$user->hasRole(1)) {
            $query->where('petugas_id', $user->id);
        }

        // DATE FILTER (FIXED)
        $date = $request->tanggal_pendaftaran ?? today()->toDateString();
        $query->whereDate('tanggal_pendaftaran', $date);

        // STATUS PEMBAYARAN FILTER (NEW)
        if ($request->filled('status_pembayaran')) {
            $query->whereHas('retribusi', function ($q) use ($request) {
                $q->where('status_pembayaran', $request->status_pembayaran);
            });
        }

        // SEARCH (SIMPLIFIED)
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('no_pendaftaran_harian', 'ilike', "%$search%")
                    ->orWhereHas('kendaraan', function ($q2) use ($search) {
                        $q2->where('no_uji', 'ilike', "%$search%")
                            ->orWhere('no_kendaraan', 'ilike', "%$search%")
                            ->orWhere('nama_pemilik', 'ilike', "%$search%");
                    })
                    ->orWhereHas('petugas', function ($q3) use ($search) {
                        $q3->where('name', 'ilike', "%$search%");
                    });
            });
        }

        if ($request->filled('status_penerbitan_id')) {
            $query->where('status_penerbitan_id', $request->status_penerbitan_id);
        }

        // DEFAULT SORT
        $primaryKey = $config['primary_key'] ?? null;

        if (!$request->filled('sort_by') && $primaryKey) {
            $request->merge([
                'sort_by' => $primaryKey,
                'sort_order' => 'desc',
            ]);
        }

        $perPage = $request->limit ?? 10;
        $result = $query->paginate($perPage);

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
                        'issuance_id',
                        'issuance_name',
                    ],
                ],

                'petugas' => [
                    'only' => [
                        'name',
                    ],
                ],

                'retribusi' => [
                    'only' => [
                        'status_pembayaran',
                    ],
                ],
            ],

            'labels' => [
                'no_pendaftaran_harian' => 'No Antrian',
                'tanggal_uji' => 'Tanggal Uji',

                'kendaraan_no_uji' => 'No Uji',
                'kendaraan_no_kendaraan' => 'No Kendaraan',
                'kendaraan_nama_pemilik' => 'Nama Pemilik',

                'status_penerbitan_issuance_name' => 'Pendaftaran',
                'petugas_name' => 'Petugas',
                'retribusi_status_pembayaran' => 'Status Pembayaran',
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
                    'field' => 'tanggal_uji',
                    'label' => 'Tanggal Uji',
                ],
                [
                    'field' => 'petugas.name',
                    'label' => 'Petugas',
                ],
                [
                    'field' => 'retribusi.jumlah_retribusi',
                    'label' => 'Jumlah Retribusi',
                ],
                [
                    'field' => 'retribusi.status_pembayaran',
                    'label' => 'Status Pembayaran',
                ],
            ],

            'sortable' => [
                'id',
                'no_pendaftaran_harian'
            ],
        ];
    }

    /**
     * Toggle bayar
     */
    public function toggleBayar($id)
    {
        $retribusi = TrnPendaftaranRetribusi::where('pendaftaran_id', $id)->firstOrFail();

        $retribusi->update([
            'status_pembayaran' => !$retribusi->status_pembayaran
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $retribusi->pendaftaran_id,
                'status_pembayaran' => $retribusi->status_pembayaran,
            ]
        ]);
    }
}
