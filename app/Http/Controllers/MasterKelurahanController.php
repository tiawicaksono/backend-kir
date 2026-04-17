<?php

namespace App\Http\Controllers;

use App\Helpers\FlattenHelper;
use App\Http\Resources\MasterKelurahanResource;
use App\Models\MasterKecamatan;
use App\Models\MasterKelurahan;
use App\Models\MasterKota;
use App\Services\QueryFilterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MasterKelurahanController extends BaseApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $model = MasterKelurahan::class;
        $query = $model::with('kecamatan', 'kecamatan.kota', 'kecamatan.kota.provinsi');

        $config = $this->getTableConfig();

        QueryFilterService::apply($query, $request, $model, $config);

        $perPage = $request->limit ?? 10;
        $result = $query->paginate($perPage);

        // 🔥 FLATTEN DATA
        $data = FlattenHelper::flatten($result->items(), $config);

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

    public function getTableConfig()
    {
        return [
            'primary_key' => 'id',
            'only' => [
                'kecamatan' => [
                    'only' => ['nama_kecamatan'],
                    'alias' => ['nama_kecamatan' => 'nama_kecamatan'],
                    'children' => [
                        'kota' => [
                            'only' => ['nama_kota'],
                            'alias' => ['nama_kota' => 'nama_kota'],
                            'children' => [
                                'provinsi' => [
                                    'only' => ['nama_provinsi'],
                                    'alias' => ['nama_provinsi' => 'nama_provinsi']
                                ]
                            ]
                        ]
                    ]
                ],
            ],
            'labels' => [
                'id' => 'ID',
                'nama_kelurahan' => 'Kelurahan',
                'nama_kecamatan' => 'Kecamatan',
                'nama_kota' => 'Kota',
                'nama_provinsi' => 'Provinsi',
            ],
            'searchable' => ['nama_kelurahan', 'kecamatan.nama_kecamatan', 'kecamatan.kota.nama_kota', 'kecamatan.kota.provinsi.nama_provinsi'],
            'sortable' => ['id', 'nama_kelurahan', 'nama_kecamatan', 'nama_kota'],
            'hidden' => ['kecamatan_id', 'created_at', 'updated_at'],
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|string|max:10|unique:master_kelurahans',
            'kecamatan_id' => 'required|string|max:6',
            'nama_kelurahan' => 'required|string|max:100'
        ]);
        if ($validator->fails()) {
            return $this->error(
                'Validation error',
                $validator->errors(),
                422
            );
        }

        try {
            $data = MasterKelurahan::create($validator->validated());
            $data->load('kecamatan');

            return $this->success(
                new MasterKelurahanResource($data),
                'Data created successfully',
                201
            );
        } catch (\Exception $e) {
            Log::error($e); // simpan detail error di log

            return $this->error('Data failed to create', null, 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $data = MasterKelurahan::find($id);

        if (!$data) {
            return $this->error('Data not found', null, 404);
        }

        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|string|max:10|unique:master_kelurahans,id,' . $id,
                'kecamatan_id' => 'required|string|max:6',
                'nama_kelurahan' => 'required|string|max:100'
            ]);

            if ($validator->fails()) {
                return $this->error(
                    'Validation error',
                    $validator->errors(),
                    422
                );
            }

            $data->update($validator->validated());
            $data->load('kecamatan');

            return $this->success(
                new MasterKelurahanResource($data),
                'Data updated successfully',
                200
            );
        } catch (\Exception $e) {
            Log::error($e);

            return $this->error(
                'Failed to update data',
                null,
                500
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = MasterKelurahan::find($id);

        if (!$data) {
            return $this->error(
                'Data not found',
                null,
                404
            );
        }

        try {
            $data->delete();

            return $this->success(
                null,
                'Data deleted successfully',
                200
            );
        } catch (\Exception $e) {
            Log::error($e);

            return $this->error(
                'Failed to delete data',
                null,
                500
            );
        }
    }

    /**
     * Get options for select input
     */
    public function options(Request $request)
    {
        $query = MasterKelurahan::query();

        if ($request->kecamatan_id) {
            $query->where('kecamatan_id', $request->kecamatan_id);
        }

        $data = $query
            ->select('id', 'nama_kelurahan')
            ->orderBy('nama_kelurahan')
            ->get()
            ->map(fn($item) => [
                'label' => $item->nama_kelurahan,
                'value' => $item->id,
            ]);

        return response()->json($data);
    }
}
