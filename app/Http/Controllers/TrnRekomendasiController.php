<?php

namespace App\Http\Controllers;

use App\Helpers\FlattenHelper;
use App\Services\QueryFilterService;
use App\Models\TrnPendaftaranRekomendasi;
use Illuminate\Http\Request;

class TrnRekomendasiController extends Controller
{
    /**
     * LIST
     */
    public function index(Request $request)
    {
        $model = TrnPendaftaranRekomendasi::class;
        $date = $request->tanggal_pendaftaran ?? today()->toDateString();
        $query = $model::query()
            ->with([
                'pendaftaran.kendaraan:id,no_uji,no_kendaraan,nama_pemilik',
                'area',
            ])
            ->whereHas('pendaftaran', function ($q) use ($date) {
                $q->whereDate('tanggal_pendaftaran', $date);
            })
            ->select('trn_pendaftaran_rekomendasis.*');

        $config = $this->getTableConfig();

        /**
         * DEFAULT SORT
         */
        $primaryKey = $config['primary_key'] ?? null;

        if (
            !$request->filled('sort_by') &&
            $primaryKey
        ) {
            $request->merge([
                'sort_by' => $primaryKey,
                'sort_order' => 'desc',
            ]);
        }

        /**
         * CUSTOM FILTER
         */
        if ($request->filled('status_sinkron')) {

            $query->where(
                'status_sinkron',
                $request->status_sinkron
            );
        }

        /**
         * APPLY GENERIC FILTER
         */
        QueryFilterService::apply(
            $query,
            $request,
            $model,
            $config
        );

        /**
         * PAGINATION
         */
        $perPage =
            $request->limit ?? 10;

        $result =
            $query->paginate($perPage);

        /**
         * FLATTEN
         */
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

            'primary_key' => 'pendaftaran_id',

            'only_fields' => [
                'pendaftaran_id',
                'no_surat_rekomendasi',
                'jenis_rekomendasi',
                'status_sinkron_label',
                'keterangan_sinkron',
            ],

            'only' => [
                'pendaftaran' => [
                    'only' => [
                        'id',
                    ],
                    'children' => [
                        'kendaraan' => [
                            'only' => [
                                'no_uji',
                                'no_kendaraan',
                            ],
                        ],
                    ],
                ],
                'area' => [
                    'only' => [
                        'area_code',
                        'area_name',
                    ],
                ],
            ],

            'labels' => [

                'no_surat_rekomendasi' =>
                'Nomor Surat',

                'jenis_rekomendasi' =>
                'Jenis Rekomendasi',

                'pendaftaran_kendaraan_no_uji' =>
                'No Uji',

                'pendaftaran_kendaraan_no_kendaraan' =>
                'No Kendaraan',

                'area_area_code' =>
                'Kode Area',

                'area_area_name' =>
                'Dishub Tujuan',

                'status_sinkron_label' =>
                'Status Sinkron',

                'keterangan_sinkron' =>
                'Keterangan',
            ],

            'searchable' => [
                [
                    'field' =>
                    'pendaftaran.kendaraan.no_uji',
                    'label' =>
                    'No Uji',
                ],
                [
                    'field' =>
                    'pendaftaran.kendaraan.no_kendaraan',
                    'label' =>
                    'No Kendaraan',
                ],
            ],

            'sortable' => [
                'pendaftaran_id',
                'no_surat_rekomendasi',
            ],
        ];
    }

    /**
     * SHOW
     */
    public function show($id)
    {
        $data = TrnPendaftaranRekomendasi::query()
            ->with([
                'pendaftaran.kendaraan:id,no_uji,no_kendaraan,nama_pemilik',
                'area',
            ])
            ->where('pendaftaran_id', $id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => [
                'pendaftaran_id' => $data->pendaftaran_id,
                'no_surat_rekomendasi' => $data->no_surat_rekomendasi,

                'jenis_rekomendasi' => $data->jenis_rekomendasi,

                'no_pemilik_tujuan' => $data->no_pemilik_tujuan,
                'nama_pemilik_tujuan' => $data->nama_pemilik_tujuan,
                'alamat_pemilik_tujuan' => $data->alamat_pemilik_tujuan,

                'provinsi_id' => $data->provinsi_id,
                'kota_id' => $data->kota_id,
                'kecamatan_id' => $data->kecamatan_id,
                'kelurahan_id' => $data->kelurahan_id,

                'area_tujuan_id' => $data->area_tujuan_id,

                'is_mutasi_keluar' => $data->is_mutasi_keluar,
                'is_numpang_keluar' => $data->is_numpang_keluar,

                'status_sinkron' => $data->status_sinkron,
                'status_sinkron_label' => $data->status_sinkron_label,
                'keterangan_sinkron' => $data->keterangan_sinkron,

                'pendaftaran_kendaraan_no_uji' =>
                $data->pendaftaran?->kendaraan?->no_uji,

                'pendaftaran_kendaraan_no_kendaraan' =>
                $data->pendaftaran?->kendaraan?->no_kendaraan,

                'area_area_code' => $data->area?->area_code,
                'area_area_name' => $data->area?->area_name,
            ],
        ]);
    }

    /**
     * UPDATE
     */
    public function update(
        Request $request,
        TrnPendaftaranRekomendasi $rekomendasi
    ) {
        $validated = $request->validate([

            'no_surat_rekomendasi' =>
            'nullable|string|max:255',

            'no_pemilik_tujuan' =>
            'nullable|string|max:50',

            'nama_pemilik_tujuan' =>
            'nullable|string|max:255',

            'alamat_pemilik_tujuan' =>
            'nullable|string',

            'provinsi_id' =>
            'nullable|integer',

            'kota_id' =>
            'nullable|integer',

            'kecamatan_id' =>
            'nullable|integer',

            'kelurahan_id' =>
            'nullable|integer',

            'area_tujuan_id' =>
            'nullable|integer',
        ]);

        // =========================
        // UPDATE
        // =========================
        $rekomendasi->update($validated);

        // =========================
        // LOAD RELATION
        // =========================
        $rekomendasi->load([
            'pendaftaran.kendaraan',
            'area',
        ]);

        // =========================
        // FLATTEN
        // =========================
        $data = [
            ...$rekomendasi->toArray(),

            // kendaraan
            'pendaftaran_kendaraan_no_uji' =>
            $rekomendasi->pendaftaran?->kendaraan?->no_uji,

            'pendaftaran_kendaraan_no_kendaraan' =>
            $rekomendasi->pendaftaran?->kendaraan?->no_kendaraan,

            // area
            'area_area_code' =>
            $rekomendasi->area?->area_code,

            'area_area_name' =>
            $rekomendasi->area?->area_name,

            // accessor
            'jenis_rekomendasi' =>
            $rekomendasi->jenis_rekomendasi,

            'status_sinkron_label' =>
            $rekomendasi->status_sinkron_label,
        ];

        // =========================
        // HAPUS NESTED RELATION
        // =========================
        unset(
            $data['pendaftaran'],
            $data['area']
        );

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * DELETE
     */
    public function destroy(
        TrnPendaftaranRekomendasi $rekomendasi
    ) {
        $rekomendasi->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * SYNC SINGLE
     */
    public function sync($id)
    {
        $rekomendasi =
            TrnPendaftaranRekomendasi::findOrFail(
                $id
            );

        /**
         * TODO:
         * HIT API KEMENTERIAN
         */

        $rekomendasi->update([
            'status_sinkron' => true,

            'keterangan_sinkron' =>
            'Berhasil sinkron',
        ]);

        return response()->json([
            'success' => true,

            'message' =>
            'Berhasil sinkron',
        ]);
    }

    /**
     * SYNC BULK
     */
    public function syncAll(
        Request $request
    ) {

        $ids = $request->ids ?? [];

        $items =
            TrnPendaftaranRekomendasi::whereIn(
                'pendaftaran_id',
                $ids
            )->get();

        foreach ($items as $item) {

            /**
             * TODO:
             * HIT API KEMENTERIAN
             */

            $item->update([
                'status_sinkron' => true,

                'keterangan_sinkron' =>
                'Berhasil sinkron',
            ]);
        }

        return response()->json([
            'success' => true,

            'message' =>
            'Bulk sinkron berhasil',
        ]);
    }
}
