<?php

namespace App\Http\Controllers;

use App\Models\MasterMerkVarianTipe;
use App\Services\KemenhubService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MasterMerkVarianTipeController extends BaseApiController
{
    public function counts()
    {
        return response()->json([
            'countData' => MasterMerkVarianTipe::count(),
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
        $buffer = [];
        $parentIds = DB::table('master_merk_varians')
            ->pluck('vehicle_varian_type_id')
            ->flip();
        $transaction = $kemenhubService->sync($payload, function ($item) use (&$buffer, $parentIds) {

            if (!isset($parentIds[$item['vehicle_varian_type_id']])) {
                return;
            }

            $buffer[] = [
                'vehicle_varian_id' => $item['vehicle_varian_id'],
                'vehicle_varian_type_id'   => $item['vehicle_varian_type_id'],
                'vehicle_varian_code' => $item['vehicle_varian_code'],
                'vehicle_varian_name' => $item['vehicle_varian_name'],
                'vehicle_varian_desc' => $item['vehicle_varian_desc'],
            ];

            // flush per 1000
            if (count($buffer) >= 1000) {
                MasterMerkVarianTipe::upsert(
                    $buffer,
                    ['vehicle_varian_id'],
                    [
                        'vehicle_varian_type_id',
                        'vehicle_varian_code',
                        'vehicle_varian_name',
                        'vehicle_varian_desc'
                    ]
                );

                $buffer = [];
            }
        });

        // 🔥 flush sisa
        if (!empty($buffer)) {
            MasterMerkVarianTipe::upsert(
                $buffer,
                ['vehicle_varian_id'],
                [
                    'vehicle_varian_type_id',
                    'vehicle_varian_code',
                    'vehicle_varian_name',
                    'vehicle_varian_desc'
                ]
            );
        }

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
        $validated = $request->validate([
            'vehicle_varian_type_id' => 'required|integer'
        ]);

        return MasterMerkVarianTipe::where('vehicle_varian_type_id', $validated['vehicle_varian_type_id'])->get();
    }

    /**
     * Display the specified resource.
     */
    public function show($masterMerkVarianTipe)
    {
        return MasterMerkVarianTipe::with('varian.merk')
            ->findOrFail($masterMerkVarianTipe);
    }

    public function options(Request $request)
    {
        $query = MasterMerkVarianTipe::query();

        if ($request->vehicle_varian_type_id) {
            $query->where('vehicle_varian_type_id', $request->vehicle_varian_type_id);
        }

        $data = $query
            ->select('vehicle_varian_id', 'vehicle_varian_name')
            ->orderBy('vehicle_varian_name')
            ->get()
            ->map(fn($item) => [
                'label' => $item->vehicle_varian_name,
                'value' => $item->vehicle_varian_id,
            ]);

        return response()->json($data);
    }
}
