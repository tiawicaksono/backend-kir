<?php

namespace App\Http\Controllers;

use App\Http\Resources\KelasJalanResource;
use App\Models\MasterKelasJalan;
use App\Services\KemenhubService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MasterKelasJalanController extends BaseApiController
{
    public function counts()
    {
        return response()->json([
            'countData' => MasterKelasJalan::count(),
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

            MasterKelasJalan::updateOrCreate(
                ['kelasjalan_id' => $item['kelasjalan_id']],
                [
                    'kelasjalan_code' => $item['kelasjalan_code'],
                    'kelasjalan_name' => $item['kelasjalan_name'],
                    'kelasjalan_desc' => $item['kelasjalan_desc'],
                    'muatan_sumbu_terberat' => $item['muatan_sumbu_terberat'],
                    'vehicle_length' => $item['vehicle_length'],
                    'vehicle_height' => $item['vehicle_height'],
                    'vehicle_width' => $item['vehicle_width']
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
        $data = MasterKelasJalan::all();
        return response()->json(KelasJalanResource::collection($data));
    }

    /**
     * Display the specified resource.
     */
    public function show(MasterKelasJalan $masterKelasJalan)
    {
        return response()->json($masterKelasJalan);
    }
}
