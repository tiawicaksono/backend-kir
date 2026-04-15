<?php

namespace App\Http\Controllers;

use App\Models\MasterMerkVarian;
use App\Services\KemenhubService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MasterMerkVarianController extends BaseApiController
{
    public function counts()
    {
        return response()->json([
            'countData' => MasterMerkVarian::count(),
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
        $parentIds = DB::table('master_merks')
            ->pluck('vehicle_brand_id')
            ->flip();
        $transaction = $kemenhubService->sync($payload, function ($item) use (&$buffer, $parentIds) {

            if (!isset($parentIds[$item['vehicle_brand_id']])) {
                return;
            }

            $buffer[] = [
                'vehicle_varian_type_id' => $item['vehicle_varian_type_id'],
                'vehicle_brand_id' => $item['vehicle_brand_id'],
                'vehicle_varian_type_code' => $item['vehicle_varian_type_code'],
                'vehicle_varian_type_name' => $item['vehicle_varian_type_name'],
                'vehicle_varian_type_desc' => $item['vehicle_varian_type_desc'],
            ];

            // flush per 1000
            if (count($buffer) >= 1000) {
                MasterMerkVarian::upsert(
                    $buffer,
                    ['vehicle_varian_type_id'],
                    [
                        'vehicle_brand_id',
                        'vehicle_varian_type_code',
                        'vehicle_varian_type_name',
                        'vehicle_varian_type_desc'
                    ]
                );

                $buffer = [];
            }
        });

        // 🔥 flush sisa
        if (!empty($buffer)) {
            MasterMerkVarian::upsert(
                $buffer,
                ['vehicle_varian_type_id'],
                [
                    'vehicle_brand_id',
                    'vehicle_varian_type_code',
                    'vehicle_varian_type_name',
                    'vehicle_varian_type_desc'
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
            'vehicle_brand_id' => 'required|integer'
        ]);

        return MasterMerkVarian::where('vehicle_brand_id', $validated['vehicle_brand_id'])->get();
    }

    /**
     * Display the specified resource.
     */
    public function show($masterMerkVarian)
    {
        return MasterMerkVarian::with('merk', 'variantipes')
            ->findOrFail($masterMerkVarian);
    }
}
