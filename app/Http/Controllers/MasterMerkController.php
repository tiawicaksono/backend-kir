<?php

namespace App\Http\Controllers;

use App\Models\MasterMerk;
use App\Models\TrnSinkron;
use App\Services\KemenhubService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterMerkController extends Controller
{
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
            'url_api' => 'required|url',
            'token'   => 'required|string'
        ]);

        $prefix = 'merk';
        try {
            $result = $this->kemenhubService->getStatusPenerbitan(
                $validated['url_api'],
                $validated['token'],
                $prefix
            );

            DB::transaction(function () use ($result, $prefix) {

                foreach ($result['data'] ?? [] as $item) {

                    MasterMerk::updateOrCreate(
                        ['vehicle_brand_id' => $item['vehicle_brand_id']],
                        [
                            'vehicle_brand_code' => $item['vehicle_brand_code'],
                            'vehicle_brand_name' => $item['vehicle_brand_name'],
                            'vehicle_brand_desc' => $item['vehicle_brand_desc']
                        ]
                    );
                }

                // history sukses
                TrnSinkron::create([
                    'name' => $prefix,
                    'status' => true,
                    'keterangan' => 'Sinkronisasi berhasil'
                ]);
            });

            return response()->json([
                'message' => 'Sinkronisasi berhasil'
            ]);
        } catch (\Exception $e) {

            // history gagal
            TrnSinkron::create([
                'name' => $prefix,
                'status' => false,
                'keterangan' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Sinkronisasi gagal',
                'error'   => $e->getMessage()
            ], 500);
        }
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
    public function show(MasterMerk $masterMerk)
    {
        return response()->json($masterMerk);
    }
}
