<?php

namespace App\Http\Controllers;

use App\Models\MasterSubJenisKendaraan;
use App\Services\KemenhubService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MasterSubJenisKendaraanController extends BaseApiController
{
    public function counts()
    {
        return response()->json([
            'countData' => MasterSubJenisKendaraan::count(),
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
        $parentIds = DB::table('master_jenis_kendaraans')
            ->pluck('vehicle_type_id')
            ->flip();
        $transaction = $kemenhubService->sync($payload, function ($item) use (&$buffer, $parentIds) {

            if (!isset($parentIds[$item['vehicle_type_id']])) {
                return;
            }

            $buffer[] = [
                'vehicle_sub_id'   => $item['vehicle_sub_id'],
                'vehicle_type_id' => $item['vehicle_type_id'],
                'vehicle_sub_code' => $item['vehicle_sub_code'],
                'vehicle_sub_name' => $item['vehicle_sub_name'],
                'vehicle_sub_desc' => $item['vehicle_sub_desc'],
            ];

            // flush per 1000
            if (count($buffer) >= 1000) {
                MasterSubJenisKendaraan::upsert(
                    $buffer,
                    ['vehicle_sub_id'],
                    [
                        'vehicle_type_id',
                        'vehicle_sub_code',
                        'vehicle_sub_name',
                        'vehicle_sub_desc'
                    ]
                );

                $buffer = [];
            }
        });

        // 🔥 flush sisa
        if (!empty($buffer)) {
            MasterSubJenisKendaraan::upsert(
                $buffer,
                ['vehicle_sub_id'],
                [
                    'vehicle_type_id',
                    'vehicle_sub_code',
                    'vehicle_sub_name',
                    'vehicle_sub_desc'
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
            'vehicle_type_id' => 'required|integer'
        ]);

        return MasterSubJenisKendaraan::where('vehicle_type_id', $validated['vehicle_type_id'])->get();
    }

    /**
     * Display the specified resource.
     */
    public function show($masterSubJenisKendaraan)
    {
        return MasterSubJenisKendaraan::with('masterJenisKendaraan')
            ->findOrFail($masterSubJenisKendaraan);
    }
}
