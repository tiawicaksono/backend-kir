<?php

namespace App\Http\Controllers;

use App\Http\Resources\TrnSinkronResource;
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
        $transaction = null;

        try {
            $result = $this->kemenhubService->getDataSync(
                $url_api,
                $token,
                $prefix
            );

            DB::transaction(function () use ($result, $api_integration_id, $prefix, $name, $url_api, $token, &$transaction) {

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
                $transaction = TrnSinkron::create([
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
                'message' => 'Sinkronisasi berhasil',
                'transaction' => new TrnSinkronResource($transaction)
            ]);
        } catch (\Exception $e) {

            // history gagal
            $transaction = TrnSinkron::create([
                'api_integration_id' => $api_integration_id,
                'name' => $name,
                'prefix' => $prefix,
                'url_api' => $url_api,
                'token' => $token,
                'status' => false,
                'keterangan' => $e->getMessage()
            ]);

            return response()->json([
                'message'   => $e->getMessage(),
                'transaction' => new TrnSinkronResource($transaction)
            ], 500);
        }
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
