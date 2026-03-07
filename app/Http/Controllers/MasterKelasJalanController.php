<?php

namespace App\Http\Controllers;

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
            'url_api' => 'required|url',
            'token'   => 'required|string'
        ]);

        $prefix = 'kelasjalan';
        try {
            $result = $this->kemenhubService->getStatusPenerbitan(
                $validated['url_api'],
                $validated['token'],
                $prefix
            );

            DB::transaction(function () use ($result, $prefix) {

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
        return MasterKelasJalan::all();
    }

    /**
     * Display the specified resource.
     */
    public function show(MasterKelasJalan $masterKelasJalan)
    {
        return response()->json($masterKelasJalan);
    }
}
