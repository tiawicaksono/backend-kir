<?php

namespace App\Http\Controllers;

use App\Helpers\FlattenHelper;
use App\Models\MasterBahanUtama;
use App\Services\QueryFilterService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MasterBahanUtamaController extends BaseApiController
{
    public function counts()
    {
        return response()->json([
            'countData' => MasterBahanUtama::count(),
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //     dd(
        //         DB::select("
        //     SELECT column_name
        //     FROM information_schema.columns
        //     WHERE table_name = 'master_bahan_utamas'
        // ")
        //     );
        $model = MasterBahanUtama::class;
        $query = $model::query();
        $config = $this->getTableConfig();

        QueryFilterService::apply($query, $request, $model, $config);
        // dd($query->toSql(), $query->getBindings());

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
            'foreign_keys' => [],
            'labels' => [
                'id' => 'ID',
                'bahan_utama' => 'Bahan Utama',
            ],
            'searchable' => ['bahan_utama'],
            'sortable' => ['id', 'bahan_utama'],
            'hidden' => ['deleted_at', 'created_at', 'updated_at'],
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
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
            $data = MasterBahanUtama::create([
                'bahan_utama' => strtoupper($validator->validated()['name'])
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
        $data = MasterBahanUtama::find($id);

        if (!$data) {
            return $this->error('Data not found', null, 404);
        }

        try {
            $validator = Validator::make($request->all(), [
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
                'bahan_utama' => $validator->validated()['name']
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
        $data = MasterBahanUtama::find($id);

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
