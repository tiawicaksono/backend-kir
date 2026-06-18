<?php

namespace App\Http\Controllers;

use App\Helpers\FlattenHelper;
use App\Models\TrnHasilUji;
use App\Models\TrnPendaftaran;
use App\Models\TrnPendaftaranRetribusi;
use Illuminate\Http\Request;

class TrnAntrianUjiController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function index(Request $request)
    {
        $model = TrnPendaftaran::class;

        $query = $model::with([
            'kendaraan:id,no_uji,no_kendaraan,nama_pemilik',
            'statusPenerbitan:issuance_id,issuance_name',
            'hasilUji:pendaftaran_id,is_datang',
        ]);

        $config = $this->getTableConfig();

        $query->whereIn('status_penerbitan_id', [1, 2, 7, 8]);

        // DATE FILTER (FIXED)
        $date = $request->tanggal_uji ?? today()->toDateString();
        $query->whereDate('tanggal_uji', $date);

        // STATUS PEMBAYARAN FILTER (NEW)
        if ($request->filled('status_datang')) {
            $query->whereHas('hasilUji', function ($q) use ($request) {
                $q->where('is_datang', $request->status_datang);
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
                    });
            });
        }

        // STATUS PENERBITAN
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

        // APPLY SORT
        $sortable = $config['sortable'] ?? [];

        $sortBy = $request->sort_by;
        $sortOrder = $request->sort_order ?? 'asc';

        if ($sortBy && in_array($sortBy, $sortable)) {
            $query->orderBy($sortBy, $sortOrder);
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

                'hasilUji' => [
                    'only' => [
                        'is_datang',
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
                'hasil_uji_is_datang' => 'Datang',
            ],

            'sortable' => [
                'id',
                'no_pendaftaran_harian'
            ],
        ];
    }

    /**
     * Toggle datang
     */
    public function toggleDatang($id)
    {
        $retribusi = TrnHasilUji::where('pendaftaran_id', $id)->firstOrFail();

        $retribusi->update([
            'is_datang' => !$retribusi->is_datang
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $retribusi->pendaftaran_id,
                'is_datang' => $retribusi->is_datang,
            ]
        ]);
    }
}
