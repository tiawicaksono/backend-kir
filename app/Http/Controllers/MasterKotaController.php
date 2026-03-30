<?php

namespace App\Http\Controllers;

use App\Helpers\FlattenHelper;
use App\Http\Resources\MasterKotaResource;
use App\Models\MasterKota;
use App\Models\MasterProvinsi;
use App\Services\QueryFilterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MasterKotaController extends BaseApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $model = MasterKota::class;
        $query = $model::with('provinsi');

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
            'foreign_keys' => ['provinsi_id'],
            'relations' => [
                'provinsi' => [
                    'model' => MasterProvinsi::class,
                    'foreign_key' => 'provinsi_id',
                    'owner_key' => 'id',
                    'columns' => ['nama_provinsi']
                ]
            ],
            'labels' => [
                'id' => 'ID',
                'nama_kota' => 'Kota',
                'nama_provinsi' => 'Provinsi',
            ],
            'searchable' => ['nama_kota', 'nama_provinsi'],
            'sortable' => ['id', 'nama_kota', 'nama_provinsi'],
            'hidden' => ['provinsi_id', 'created_at', 'updated_at'],
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|string|max:4|unique:master_kotas',
            'provinsi_id' => 'required|string|max:2',
            'nama_kota' => 'required|string|max:100'
        ]);
        if ($validator->fails()) {
            return $this->error(
                'Validation error',
                $validator->errors(),
                422
            );
        }

        try {
            $data = MasterKota::create($validator->validated());
            $data->load('provinsi');

            return $this->success(
                new MasterKotaResource($data),
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
        $data = MasterKota::find($id);

        if (!$data) {
            return $this->error('Data not found', null, 404);
        }

        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|string|max:4|unique:master_kotas,id,' . $id,
                'provinsi_id' => 'required|string|max:2',
                'nama_kota' => 'required|string|max:100'
            ]);

            if ($validator->fails()) {
                return $this->error(
                    'Validation error',
                    $validator->errors(),
                    422
                );
            }

            $data->update($validator->validated());
            $data->load('provinsi');

            return $this->success(
                new MasterKotaResource($data),
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
        $data = MasterKota::find($id);

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
