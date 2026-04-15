<?php

namespace App\Http\Controllers;

use App\Models\MasterPegawai;
use App\Services\KemenhubService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MasterPegawaiController extends BaseApiController
{
    public function counts()
    {
        return response()->json([
            'countData' => MasterPegawai::count(),
        ]);
    }

    public function sync(Request $request, KemenhubService $kemenhubService)
    {
        $validator = Validator::make($request->all(), [
            'api_integration_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'prefix' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return $this->error(
                'Validation error',
                $validator->errors(),
                422
            );
        }

        $payload = $validator->validated();

        $transaction = $kemenhubService->sync($payload, function ($item) {

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
        });

        return response()->json(
            $transaction,
            $transaction['success'] ? 200 : 500
        );
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
