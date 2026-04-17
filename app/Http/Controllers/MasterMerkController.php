<?php

namespace App\Http\Controllers;

use App\Models\MasterMerk;
use App\Services\KemenhubService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MasterMerkController extends BaseApiController
{
    public function counts()
    {
        return response()->json([
            'countData' => MasterMerk::count(),
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

            MasterMerk::updateOrCreate(
                ['vehicle_brand_id' => $item['vehicle_brand_id']],
                [
                    'vehicle_brand_code' => $item['vehicle_brand_code'],
                    'vehicle_brand_name' => $item['vehicle_brand_name'],
                    'vehicle_brand_desc' => $item['vehicle_brand_desc']
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
        return MasterMerk::all();
    }

    /**
     * Display the specified resource.
     */
    public function show($masterMerk)
    {
        return MasterMerk::with('varians.variantipes')
            ->findOrFail($masterMerk);
    }

    /**
     * Get options for select input
     */
    public function options()
    {
        $data = MasterMerk::select('vehicle_brand_id', 'vehicle_brand_name')
            ->orderBy('vehicle_brand_name')
            ->get()
            ->map(fn($item) => [
                'label' => $item->vehicle_brand_name,
                'value' => $item->vehicle_brand_id,
            ]);

        return response()->json($data);
    }
}
