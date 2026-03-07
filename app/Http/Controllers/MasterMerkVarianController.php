<?php

namespace App\Http\Controllers;

use App\Models\MasterMerkVarian;
use App\Models\TrnSinkron;
use App\Services\KemenhubService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterMerkVarianController extends Controller
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

        $prefix = 'variantype';
        try {
            $result = $this->kemenhubService->getStatusPenerbitan(
                $validated['url_api'],
                $validated['token'],
                $prefix
            );

            DB::transaction(function () use ($result) {

                // ambil semua parent id yang ada
                $parentIds = DB::table('master_merks')
                    ->pluck('vehicle_brand_id')
                    ->toArray();

                foreach ($result['data'] ?? [] as $item) {

                    // jika parent tidak ada → skip
                    if (!in_array($item['vehicle_brand_id'], $parentIds)) {
                        continue;
                    }

                    $data[] = [
                        'vehicle_varian_type_id' => $item['vehicle_varian_type_id'],
                        'vehicle_brand_id'   => $item['vehicle_brand_id'],
                        'vehicle_varian_type_code' => $item['vehicle_varian_type_code'],
                        'vehicle_varian_type_name' => $item['vehicle_varian_type_name'],
                        'vehicle_varian_type_desc' => $item['vehicle_varian_type_desc'],
                    ];
                }

                MasterMerkVarian::upsert(
                    $data,
                    ['vehicle_varian_type_id'],
                    ['vehicle_brand_id', 'vehicle_varian_type_code', 'vehicle_varian_type_name', 'vehicle_varian_type_desc']
                );

                // history sukses
                TrnSinkron::create([
                    'name' => 'varian',
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
                'name' => 'varian',
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
        return MasterMerkVarian::all();
    }

    /**
     * Display the specified resource.
     */
    public function show(MasterMerkVarian $masterMerkVarian)
    {
        return response()->json($masterMerkVarian);
    }
}
