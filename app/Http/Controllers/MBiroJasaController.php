<?php

namespace App\Http\Controllers;

use App\Helpers\FlattenHelper;
use App\Models\MBiroJasa;
use App\Services\QueryFilterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MBiroJasaController extends BaseApiController
{
    public function counts()
    {
        return response()->json([
            'countData' => MBiroJasa::count(),
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $model = MBiroJasa::class;
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
            'only_fields' => ['name', 'company', 'no_hp'],
            'labels' => [
                'name' => 'Nama Biro Jasa',
                'company' => 'Perusahaan',
                'no_hp' => 'Nomor HP',
            ],
            'searchable' => [[
                'field' => 'name',
                'label' => 'Nama Biro Jasa'
            ], [
                'field' => 'company',
                'label' => 'Perusahaan'
            ]],
            'sortable' => ['name', 'company'],
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'company' => 'required|string|max:100',
            'no_hp' => 'required|string|max:20|unique:m_biro_jasas,no_hp'
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
            $data = MBiroJasa::create([
                'name' => strtoupper($validator->validated()['name']),
                'company' => strtoupper($validator->validated()['company']),
                'no_hp' => $validator->validated()['no_hp']
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
        $data = MBiroJasa::find($id);

        if (!$data) {
            return $this->error('Data not found', null, 404);
        }

        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100',
                'company' => 'required|string|max:100',
                'no_hp' => 'required|string|max:20|unique:m_biro_jasas,no_hp,' . $id
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
                'name' => $validator->validated()['name'],
                'company' => $validator->validated()['company'],
                'no_hp' => $validator->validated()['no_hp']
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
        $data = MBiroJasa::find($id);

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
        $data = MBiroJasa::select('id', 'name', 'company')
            ->orderBy('name')
            ->get()
            ->map(fn($item) => [
                'label' => $item->name . ' - ' . $item->company,
                'value' => $item->id,
            ]);

        return response()->json($data);
    }
}
