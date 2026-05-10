<?php

namespace App\Http\Controllers;

use App\Models\MasterArea;
use App\Services\KemenhubService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MasterAreaController extends BaseApiController
{
    public function counts()
    {
        return response()->json([
            'countData' => MasterArea::count(),
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

            MasterArea::updateOrCreate(
                ['area_id' => $item['area_id']],
                [
                    'area_code' => $item['area_code'],
                    'area_name' => $item['area_name'],
                    'area_address' => $item['area_address'],
                    'area_email' => $item['area_email'],
                    'area_pic' => $item['area_pic'],
                    'area_active' => $item['area_active'],
                    'area_logo_active' => $item['area_logo_active'],
                    'logo' => $item['logo'],
                    'logo_gray' => $item['logo_gray'],
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
        return MasterArea::all();
    }

    /**
     * Display the specified resource.
     */
    public function show(MasterArea $masterArea)
    {
        return response()->json($masterArea);
    }

    /**
     * Get options for select input
     */
    public function options()
    {
        $data = MasterArea::select('area_id', 'area_name')
            ->orderBy('area_name')
            ->get()
            ->map(fn($item) => [
                'label' => $item->area_name,
                'value' => $item->area_id,
            ]);

        return response()->json($data);
    }
}
