<?php

namespace App\Http\Controllers;

use App\Models\MasterArea;
use App\Models\TrnSinkron;
use App\Services\KemenhubService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterAreaController extends Controller
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

        $prefix = 'area';
        try {
            $result = $this->kemenhubService->getStatusPenerbitan(
                $validated['url_api'],
                $validated['token'],
                $prefix
            );

            DB::transaction(function () use ($result, $prefix) {

                foreach ($result['data'] ?? [] as $item) {

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
        return MasterArea::all();
    }

    /**
     * Display the specified resource.
     */
    public function show(MasterArea $masterArea)
    {
        return response()->json($masterArea);
    }
}
