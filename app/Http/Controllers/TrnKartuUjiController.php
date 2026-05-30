<?php

namespace App\Http\Controllers;

use App\Helpers\FlattenHelper;
use App\Models\TrnPendaftaran;
use App\Services\QueryFilterService;
use Illuminate\Http\Request;

class TrnKartuUjiController extends Controller
{

    public function counts()
    {
        return response()->json([
            'countData' => TrnPendaftaran::whereIn('status_penerbitan_id', [3, 4])->count(),
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $model = TrnPendaftaran::class;

        $query = $model::query()
            ->with([
                'kendaraan:id,no_uji,no_kendaraan,nama_pemilik',

                'statusPenerbitan:issuance_id,issuance_name',

                'kartuUji:pendaftaran_id,tanggal_cetak,petugas_id',

                'kartuUji.petugas:id,name',
            ])
            ->whereIn('status_penerbitan_id', [3, 4]);

        $config = $this->getTableConfig();

        /**
         * =========================================
         * 🔥 DATE FILTER
         * =========================================
         */
        $date = $request->tanggal_pendaftaran
            ?? today()->toDateString();

        $query->whereDate(
            'tanggal_pendaftaran',
            $date
        );

        /**
         * =========================================
         * 🔥 REMOVE DATE
         * supaya tidak double filter
         * =========================================
         */
        $request->request->remove(
            'tanggal_pendaftaran'
        );

        /**
         * =========================================
         * 🔥 GENERIC FILTER
         * =========================================
         */
        QueryFilterService::apply(
            $query,
            $request,
            $model,
            $config
        );

        /**
         * =========================================
         * 🔥 PAGINATION
         * =========================================
         */
        $perPage = $request->limit ?? 10;

        $result = $query->paginate($perPage);

        /**
         * =========================================
         * 🔥 FLATTEN
         * =========================================
         */
        $data = collect(
            FlattenHelper::flatten(
                $result->items(),
                $config
            )
        )->map(function ($item) {

            /**
             * =====================================
             * 🔥 STATUS CETAK
             * jika data ada di kartu_uji
             * =====================================
             */
            $item['status_cetak'] =
                !empty($item['kartu_uji_tanggal_cetak']);

            return $item;
        })->values();

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

                'kartuUji' => [

                    'only' => [
                        'tanggal_cetak',
                    ],

                    'children' => [

                        'petugas' => [

                            'only' => [
                                'name',
                            ],
                        ],
                    ],
                ],
            ],

            'labels' => [

                'no_pendaftaran_harian' => 'No Antrian',

                'kendaraan_no_uji' => 'No Uji',
                'kendaraan_no_kendaraan' => 'No Kendaraan',
                'kendaraan_nama_pemilik' => 'Nama Pemilik',

                'status_penerbitan_issuance_name'
                => 'Status Penerbitan',

                'kartu_uji_tanggal_cetak'
                => 'Tanggal Cetak',

                'kartu_uji_petugas_name'
                => 'Petugas Cetak',

                'status_cetak'
                => 'Status Cetak',
            ],

            /**
             * =====================================
             * 🔥 SEARCHABLE
             * =====================================
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
                    'field'
                    => 'statusPenerbitan.issuance_name',

                    'label'
                    => 'Status Penerbitan',
                ],
            ],

            /**
             * =====================================
             * 🔥 SORTABLE
             * =====================================
             */
            'sortable' => [
                'id',
                'no_pendaftaran_harian',
            ],
        ];
    }

    public function print($id)
    {
        echo $id;
    }
}
