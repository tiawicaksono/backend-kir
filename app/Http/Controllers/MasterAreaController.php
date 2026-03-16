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
