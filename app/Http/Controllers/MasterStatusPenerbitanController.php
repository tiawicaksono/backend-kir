<?php

namespace App\Http\Controllers;

use App\Helpers\FlattenHelper;
use App\Models\MasterStatusPenerbitan;
use App\Services\KemenhubService;
use App\Services\QueryFilterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MasterStatusPenerbitanController extends BaseApiController
{
    public function counts()
    {
        return response()->json([
            'countData' => MasterStatusPenerbitan::count(),
        ]);
    }

    public function sync(Request $request, KemenhubService $kemenhubService)
    {
        $validator = Validator::make($request->all(), [
            'api_integration_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'prefix' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return $this->error(
                'Validation error',
                $validator->errors(),
                422
            );
        }

        $payload = $validator->validated();

        $transaction = $kemenhubService->sync($payload, function ($item) {
            MasterStatusPenerbitan::updateOrCreate(
                ['issuance_id' => $item['issuance_id']],
                [
                    'issuance_code' => $item['issuance_code'],
                    'issuance_name' => $item['issuance_name'],
                    'issuance_desc' => $item['issuance_desc']
                ]
            );
        });

        return response()->json(
            $transaction,
            $transaction['success'] ? 200 : 500
        );
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $model = MasterStatusPenerbitan::class;
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
            'primary_key' => 'issuance_id',
            'labels' => [
                'issuance_id' => 'ID',
                'issuance_name' => 'Status Penerbitan'
            ],
            'searchable' => [['field' => 'issuance_name', 'label' => 'Status Penerbitan']],
            'sortable' => ['issuance_id', 'issuance_code', 'issuance_name'],
            'hidden' => ['issuance_desc', 'created_at', 'updated_at'],
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
            $data = DB::transaction(function () use ($validator) {

                // 🔥 ambil row terakhir + lock
                $last = DB::table('master_status_penerbitans')
                    ->orderByDesc('issuance_id')
                    ->lockForUpdate()
                    ->first();

                $nextId = ($last->issuance_id ?? 0) + 1;

                return MasterStatusPenerbitan::create([
                    'issuance_id'   => $nextId,
                    'issuance_code' => '-',
                    'issuance_name' => $validator->validated()['name'],
                    'issuance_desc' => null,
                ]);
            });

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
        $data = MasterStatusPenerbitan::find($id);

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

            // ❗ jika issuance_code bukan '-' → tidak boleh update
            if ($data->issuance_code !== '-') {
                return $this->error(
                    'Data tidak bisa dihapus karena data kementrian',
                    null,
                    400
                );
            }

            // $data->update($validator->validated());
            $data->update([
                'issuance_name' => $validator->validated()['name']
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
        $data = MasterStatusPenerbitan::find($id);

        if (!$data) {
            return $this->error(
                'Data not found',
                null,
                404
            );
        }

        try {
            // ❗ jika issuance_code bukan '-' → tidak boleh delete
            if ($data->issuance_code !== '-') {
                return $this->error(
                    'Data tidak bisa dihapus karena data kementrian',
                    null,
                    400
                );
            }

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
        $data = MasterStatusPenerbitan::select(
            'issuance_id',
            'issuance_name'
        )
            ->orderBy('issuance_name')
            ->get()
            ->map(function ($item) {

                // 🔑 mapping ID → CODE
                $code = match ($item->issuance_id) {
                    1 => 'PERTAMA',
                    2 => 'BERKALA',
                    3 => 'MUTASI',
                    4 => 'NUMPANG',
                    default => 'UNKNOWN',
                };

                // 🔥 RULE DI BACKEND
                $allowedFor = match ($code) {
                    'PERTAMA' => ['not_found'],
                    'BERKALA', 'MUTASI', 'NUMPANG' => ['found'],
                    default => [],
                };

                return [
                    'label' => $item->issuance_name,
                    'value' => $item->issuance_id,
                    'code' => $code,
                    'allowed_for' => $allowedFor,
                ];
            });

        return response()->json($data);
    }
}
