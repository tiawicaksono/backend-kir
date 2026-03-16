<?php

namespace App\Http\Controllers;

use App\Http\Resources\KelasJalanResource;
use App\Models\MasterKelasJalan;
use App\Models\TrnSinkron;
use App\Services\KemenhubService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterKelasJalanController extends Controller
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

                foreach ($result['data'] ?? [] as $item) {

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
                }

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
