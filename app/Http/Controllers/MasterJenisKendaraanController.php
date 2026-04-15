<?php

namespace App\Http\Controllers;

use App\Models\MasterJenisKendaraan;
use App\Services\KemenhubService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MasterJenisKendaraanController extends BaseApiController
{
    public function counts()
    {
        return response()->json([
            'countData' => MasterJenisKendaraan::count(),
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

            MasterJenisKendaraan::updateOrCreate(
                ['vehicle_type_id' => $item['vehicle_type_id']],
                [
                    'vehicle_type_code' => $item['vehicle_type_code'],
                    'vehicle_type_name' => $item['vehicle_type_name'],
                    'vehicle_type_desc' => $item['vehicle_type_desc']
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
    public function index()
    {
        return MasterJenisKendaraan::all();
    }

    /**
     * Display the specified resource.
     */
    public function show($masterJenisKendaraan)
    {
        return MasterJenisKendaraan::with('masterSubJenisKendaraans')
            ->findOrFail($masterJenisKendaraan);
    }
}
