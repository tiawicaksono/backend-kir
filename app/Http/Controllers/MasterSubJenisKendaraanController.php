<?php

namespace App\Http\Controllers;

use App\Models\MasterSubJenisKendaraan;
use App\Models\TrnSinkron;
use App\Services\KemenhubService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterSubJenisKendaraanController extends Controller
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

        $name = 'Sub Jenis Kendaraan';
        $prefix = 'subvehicletype';
        $url_api = $validated['url_api'];
        $token = $validated['token'];
        try {
            $result = $this->kemenhubService->getStatusPenerbitan(
                $url_api,
                $token,
                $prefix
            );

            DB::transaction(function () use ($result, $prefix, $name, $url_api, $token) {

                // ambil semua parent id yang ada
                $parentIds = DB::table('master_jenis_kendaraans')
                    ->pluck('vehicle_type_id')
                    ->toArray();

                foreach ($result['data'] ?? [] as $item) {

                    // jika parent tidak ada → skip
                    if (!in_array($item['vehicle_type_id'], $parentIds)) {
                        continue;
                    }

                    $data[] = [
                        'vehicle_sub_id' => $item['vehicle_sub_id'],
                        'vehicle_type_id'   => $item['vehicle_type_id'],
                        'vehicle_sub_code' => $item['vehicle_sub_code'],
                        'vehicle_sub_name' => $item['vehicle_sub_name'],
                        'vehicle_sub_desc' => $item['vehicle_sub_desc'],
                    ];
                }

                MasterSubJenisKendaraan::upsert(
                    $data,
                    ['vehicle_sub_id'],
                    ['vehicle_type_id', 'vehicle_sub_code', 'vehicle_sub_name', 'vehicle_sub_desc']
                );

                // history sukses
                TrnSinkron::create([
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
    public function index()
    {
        return MasterSubJenisKendaraan::all();
    }

    /**
     * Display the specified resource.
     */
    public function show(MasterSubJenisKendaraan $masterSubJenisKendaraan)
    {
        return response()->json($masterSubJenisKendaraan);
    }
}
