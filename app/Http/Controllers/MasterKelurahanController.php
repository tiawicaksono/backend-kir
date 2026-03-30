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
        $query = $model::with('kecamatan.kota');

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
            'foreign_keys' => ['kecamatan_id'],
            'relations' => [
                'kecamatan' => [
                    'model' => MasterKecamatan::class,
                    'foreign_key' => 'kecamatan_id',
                    'owner_key' => 'id',
                    'columns' => ['nama_kecamatan']
                ],
                // 🔥 NESTED RELATION
                'kecamatan.kota' => [
                    'model' => MasterKota::class,
                    'foreign_key' => 'kota_id',
                    'owner_key' => 'id',
                    'columns' => ['nama_kota']
                ]
            ],
            'labels' => [
                'id' => 'ID',
                'nama_kelurahan' => 'Kelurahan',
                'nama_kecamatan' => 'Kecamatan',
                'nama_kota' => 'Kota',
            ],
            'searchable' => ['nama_kelurahan', 'nama_kecamatan', 'nama_kota'],
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
}
