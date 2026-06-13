<?php

namespace App\Http\Controllers;

use App\Helpers\FlattenHelper;
use App\Models\MKuota;
use App\Services\QueryFilterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class KuotaController extends BaseApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $model = MKuota::class;
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
            'only_fields' => ['id', 'kuota'],
            'labels' => [
                'kuota' => 'Kuota',
            ],
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kuota' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return $this->error(
                'Validation error',
                $validator->errors(),
                422
            );
        }

        try {
            $data = MKuota::create([
                'kuota' => $validator->validated()['kuota']
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
        $data = MKuota::find($id);

        if (!$data) {
            return $this->error('Data not found', null, 404);
        }

        try {
            $validator = Validator::make($request->all(), [
                'kuota' => 'required|integer'
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
                'kuota' => $validator->validated()['kuota']
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
}
