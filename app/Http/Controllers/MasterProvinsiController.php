<?php

namespace App\Http\Controllers;

use App\Helpers\FlattenHelper;
use App\Models\MasterKecamatan;
use App\Models\MasterKelurahan;
use App\Models\MasterKota;
use App\Models\MasterProvinsi;
use App\Services\QueryFilterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MasterProvinsiController extends BaseApiController
{
    public function counts()
    {
        return response()->json([
            'provinsi' => MasterProvinsi::count(),
            'kota' => MasterKota::count(),
            'kecamatan' => MasterKecamatan::count(),
            'kelurahan' => MasterKelurahan::count(),
        ]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $model = MasterProvinsi::class;
        $query = $model::query();

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
            'labels' => [
                'id' => 'ID',
                'nama_provinsi' => 'Provinsi',
            ],
            'searchable' => ['nama_provinsi'],
            'sortable' => ['id', 'nama_provinsi'],
            'hidden' => ['created_at', 'updated_at'],
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|string|max:2|unique:master_provinsis',
            'name' => 'required|string|max:100'
        ]);
        if ($validator->fails()) {
            return $this->error(
                'Validation error',
                $validator->errors(),
                422
            );
        }

        try {
            // $data = MasterProvinsi::create($validator->validated());
            $data = MasterProvinsi::create([
                'id' => $validator->validated()['id'],
                'nama_provinsi' => $validator->validated()['name']
            ]);

            return $this->success(
                $data,
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
        $data = MasterProvinsi::find($id);

        if (!$data) {
            return $this->error('Data not found', null, 404);
        }

        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|string|max:2|unique:master_provinsis,id,' . $id,
                'name' => 'required|string|max:100'
            ]);

            if ($validator->fails()) {
                return $this->error(
                    'Validation error',
                    $validator->errors(),
                    422
                );
            }

            // $data->update($validator->validated());
            $data->update([
                'id' => $validator->validated()['id'],
                'nama_provinsi' => $validator->validated()['name']
            ]);

            return $this->success(
                $data,
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
        $data = MasterProvinsi::find($id);

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
    public function options()
    {
        $data = MasterProvinsi::select('id', 'nama_provinsi')
            ->orderBy('nama_provinsi')
            ->get()
            ->map(fn($item) => [
                'label' => $item->nama_provinsi,
                'value' => $item->id,
            ]);

        return response()->json($data);
    }
}
