<?php

namespace App\Http\Controllers;

use App\Models\MasterStatusPenerbitan;
use App\Models\TrnSinkron;
use App\Services\KemenhubService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterStatusPenerbitanController extends Controller
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

        $prefix = 'statuspenerbitan';
        try {
            $result = $this->kemenhubService->getStatusPenerbitan(
                $validated['url_api'],
                $validated['token'],
                $prefix
            );

            DB::transaction(function () use ($result, $prefix) {

                foreach ($result['data'] ?? [] as $item) {

                    MasterStatusPenerbitan::updateOrCreate(
                        ['issuance_id' => $item['issuance_id']],
                        [
                            'issuance_code' => $item['issuance_code'],
                            'issuance_name' => $item['issuance_name'],
                            'issuance_desc' => $item['issuance_desc']
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
        return MasterStatusPenerbitan::all();
    }

    /**
     * Display the specified resource.
     */
    public function show(MasterStatusPenerbitan $masterStatusPenerbitan)
    {
        return response()->json($masterStatusPenerbitan);
    }
}
