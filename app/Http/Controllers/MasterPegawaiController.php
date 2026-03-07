<?php

namespace App\Http\Controllers;

use App\Models\MasterPegawai;
use App\Models\TrnSinkron;
use App\Services\KemenhubService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterPegawaiController extends Controller
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

        $prefix = 'pegawai';
        try {
            $result = $this->kemenhubService->getStatusPenerbitan(
                $validated['url_api'],
                $validated['token'],
                $prefix
            );

            DB::transaction(function () use ($result, $prefix) {

                foreach ($result['data'] ?? [] as $item) {

                    MasterPegawai::updateOrCreate(
                        ['user_id' => $item['user_id']],
                        [
                            'job_type_id' => $item['job_type_id'],
                            'job_type_code' => $item['job_type_code'],
                            'job_type_name' => $item['job_type_name'],
                            'job_id' => $item['job_id'],
                            'job_code' => $item['job_code'],
                            'job_name' => $item['job_name'],
                            'identity_number' => $item['identity_number'],
                            'full_name' => $item['full_name'],
                            'pangkat' => $item['pangkat'],
                            'email' => $item['email'],
                            'phone_number' => $item['phone_number'],
                            'address' => $item['address'],
                            'sign_active' => $item['sign_active'],
                            'sign1' => $item['sign1'],
                            'sign2' => $item['sign2'],
                            'sign3' => $item['sign3'],
                            'job_active' => $item['job_active'],
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
        return MasterPegawai::all();
    }

    /**
     * Display the specified resource.
     */
    public function show(MasterPegawai $masterPegawai)
    {
        return response()->json($masterPegawai);
    }
}
