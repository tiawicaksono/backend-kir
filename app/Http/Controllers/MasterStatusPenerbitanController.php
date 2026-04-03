<?php

namespace App\Http\Controllers;

use App\Helpers\FlattenHelper;
use App\Http\Resources\TrnSinkronResource;
use App\Models\MasterStatusPenerbitan;
use App\Models\TrnSinkron;
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

    /**
     * Sinkronisasi status penerbitan dari external dan simpan ke database
     */
    protected $kemenhubService;

    public function __construct(KemenhubService $kemenhubService)
    {
        $this->kemenhubService = $kemenhubService;
    }

    public function sync(Request $request)
    {
        $validated = $request->validate([
            'api_integration_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'prefix' => 'required|string|max:255',
            'url_api' => 'required|url',
            'token'   => 'required|string'
        ]);

        $api_integration_id = $validated['api_integration_id'];
        $name = $validated['name'];
        $prefix = $validated['prefix'];
        $url_api = $validated['url_api'];
        $token = $validated['token'];
        $transaction = null;
        try {
            $result = $this->kemenhubService->getDataSync(
                $url_api,
                $token,
                $prefix
            );

            DB::transaction(function () use ($result, $api_integration_id, $prefix, $name, $url_api, $token, &$transaction) {

                foreach ($result['data'] ?? [] as $item) {
                    // throw new \Exception("Test error");
                    MasterStatusPenerbitan::updateOrCreate(
                        ['issuance_id' => $item['issuance_id']],
                        [
                            'issuance_code' => $item['issuance_code'],
                            'issuance_name' => $item['issuance_name'],
                            'issuance_desc' => $item['issuance_desc']
                        ]
                    );
                }

                // history sukses
                $transaction = TrnSinkron::create([
                    'api_integration_id' => $api_integration_id,
                    'name' => $name,
                    'prefix' => $prefix,
                    'url_api' => $url_api,
                    'token' => $token,
                    'status' => true,
                    'keterangan' => 'Sinkronisasi berhasil',
                ]);
            });

            return response()->json([
                'message' => 'Sinkronisasi berhasil',
                'transaction' => new TrnSinkronResource($transaction)
            ]);
        } catch (\Exception $e) {

            // history gagal
            $transaction = TrnSinkron::create([
                'api_integration_id' => $api_integration_id,
                'name' => $name,
                'prefix' => $prefix,
                'url_api' => $url_api,
                'token' => $token,
                'status' => false,
                'keterangan' => $e->getMessage()
            ]);

            return response()->json([
                'message' => $e->getMessage(),
                'transaction' => new TrnSinkronResource($transaction)
            ], 500);
        }
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
            'foreign_keys' => [],
            'labels' => [
                'issuance_id' => 'ID',
                'issuance_name' => 'Status Penerbitan'
            ],
            'searchable' => ['issuance_name'],
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
}
