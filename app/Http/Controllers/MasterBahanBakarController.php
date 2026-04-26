<?php

namespace App\Http\Controllers;

use App\Models\MasterBahanBakar;
use App\Services\KemenhubService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MasterBahanBakarController extends BaseApiController
{
    public function counts()
    {
        return response()->json([
            'countData' => MasterBahanBakar::count(),
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

            MasterBahanBakar::updateOrCreate(
                ['fuel_id' => $item['fuel_id']],
                [
                    'fuel_name' => $item['fuel_name'],
                    'fuel_desc' => $item['fuel_desc']
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
        return MasterBahanBakar::all();
    }

    /**
     * Display the specified resource.
     */
    public function show(MasterBahanBakar $masterBahanBakar)
    {
        return response()->json($masterBahanBakar);
    }

    /**
     * Get options for select input
     */
    public function options()
    {
        $data = MasterBahanBakar::select('fuel_id', 'fuel_name')
            ->orderBy('fuel_id')
            ->get()
            ->map(fn($item) => [
                'label' => $item->fuel_name,
                'value' => $item->fuel_id,
            ]);

        return response()->json($data);
    }
}
