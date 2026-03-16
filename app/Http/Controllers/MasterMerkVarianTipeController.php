<?php

namespace App\Http\Controllers;

use App\Models\MasterMerkVarianTipe;
use App\Models\TrnSinkron;
use App\Services\KemenhubService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterMerkVarianTipeController extends Controller
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
            'api_integration_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'prefix' => 'required|string|max:255',
            'url_api' => 'required|url',
            'token'   => 'required|string'
        ]);

        $api_integration_id = $validated['api_integration_id'];
        $name = $validated['name'];
        $prefix = $validated['prefix'];
        $url_api = $validated['url_api'];
        $token = $validated['token'];
        try {
            $result = $this->kemenhubService->getStatusPenerbitan(
                $url_api,
                $token,
                $prefix
            );

            DB::transaction(function () use ($result, $api_integration_id, $prefix, $name, $url_api, $token) {

                // ambil semua parent id yang ada
                $parentIds = DB::table('master_merk_varians')
                    ->pluck('vehicle_varian_type_id')
                    ->toArray();

                foreach ($result['data'] ?? [] as $item) {

                    // jika parent tidak ada → skip
                    if (!in_array($item['vehicle_varian_type_id'], $parentIds)) {
                        continue;
                    }

                    $data[] = [
                        'vehicle_varian_id' => $item['vehicle_varian_id'],
                        'vehicle_varian_type_id'   => $item['vehicle_varian_type_id'],
                        'vehicle_varian_code' => $item['vehicle_varian_code'],
                        'vehicle_varian_name' => $item['vehicle_varian_name'],
                        'vehicle_varian_desc' => $item['vehicle_varian_desc'],
                    ];
                }

                MasterMerkVarianTipe::upsert(
                    $data,
                    ['vehicle_varian_id'],
                    ['vehicle_varian_type_id', 'vehicle_varian_code', 'vehicle_varian_name', 'vehicle_varian_desc']
                );

                // history sukses
                TrnSinkron::create([
                    'api_integration_id' => $api_integration_id,
                    'name' => $name,
                    'prefix' => $prefix,
                    'url_api' => $url_api,
                    'token' => $token,
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
                'api_integration_id' => $api_integration_id,
                'name' => $name,
                'prefix' => $prefix,
                'url_api' => $url_api,
                'token' => $token,
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
}
