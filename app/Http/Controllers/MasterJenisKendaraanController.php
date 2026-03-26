<?php

namespace App\Http\Controllers;

use App\Http\Resources\TrnSinkronResource;
use App\Models\MasterJenisKendaraan;
use App\Models\TrnSinkron;
use App\Services\KemenhubService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterJenisKendaraanController extends Controller
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

                foreach ($result['data'] ?? [] as $item) {

                    MasterJenisKendaraan::updateOrCreate(
                        ['vehicle_type_id' => $item['vehicle_type_id']],
                        [
                            'vehicle_type_code' => $item['vehicle_type_code'],
                            'vehicle_type_name' => $item['vehicle_type_name'],
                            'vehicle_type_desc' => $item['vehicle_type_desc']
                        ]
                    );
                }

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
