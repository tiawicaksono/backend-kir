<?php

namespace App\Http\Controllers;

use App\Http\Resources\TrnSinkronResource;
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
