<?php

namespace App\Http\Controllers;

use App\Http\Resources\ApiKeyResource;
use App\Models\ApiToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ApiTokenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = ApiToken::orderByDesc('id')->get();
        return response()->json(ApiKeyResource::collection($data));
    }

    // public function index()
    // {
    //     $start = microtime(true);

    //     DB::connection()->getPdo();

    //     logger('DB connect: ' . (microtime(true) - $start));

    //     $start2 = microtime(true);

    //     $data = ApiToken::orderByDesc('id')->get();

    //     logger('Query: ' . (microtime(true) - $start2));

    //     return response()->json(ApiKeyResource::collection($data));
    // }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'urlApi' => 'required|url|max:255',
            'token' => 'required|string|unique:m_api_tokens',
        ]);

        $data = [
            'name'    => $validated['name'],
            'url_api' => $validated['urlApi'],
            'token'   => $validated['token'],
            'is_active' => false
        ];

        $apiToken = ApiToken::create($data);

        return response()->json([
            'success' => true,
            'message' => 'API Token created successfully',
            'data' => new ApiKeyResource($apiToken)
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $apiToken = ApiToken::findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'API Token found successfully',
            'data' => $apiToken
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $apiToken = ApiToken::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'urlApi' => 'required|url|max:255',
            'token' => 'required|string',
        ]);

        $data = [
            'name'    => $validated['name'],
            'url_api' => $validated['urlApi'],
            'token'   => $validated['token']
        ];

        $apiToken->update($data);

        return response()->json([
            'success' => true,
            'message' => 'API Token updated successfully',
            'data' => new ApiKeyResource($apiToken)
        ]);
    }
    public function updateStatus(Request $request)
    {
        // ✅ Validation
        $validated = $request->validate([
            'id' => [
                'required',
                'integer',
                Rule::exists((new ApiToken)->getTable(), 'id'),
            ],
            'is_active' => 'required|boolean',
        ]);

        return DB::transaction(function () use ($validated) {

            $apiToken = ApiToken::findOrFail($validated['id']);

            // 🔥 CASE 1: Aktifkan token ini
            if ($validated['is_active']) {

                // nonaktifkan semua
                ApiToken::where('is_active', true)->update([
                    'is_active' => false
                ]);

                // aktifkan yang dipilih
                $apiToken->update([
                    'is_active' => true
                ]);
            }

            // 🔥 CASE 2: Nonaktifkan token ini
            else {

                // cek apakah ini satu-satunya yang aktif
                $activeCount = ApiToken::where('is_active', true)->count();

                if ($activeCount <= 1 && $apiToken->is_active) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Minimal harus ada 1 API Token yang aktif'
                    ], 422);
                }

                $apiToken->update([
                    'is_active' => false
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'API Token status updated successfully',
                'data' => $apiToken
            ]);
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $apiToken = ApiToken::findOrFail($id);
        $apiToken->delete();

        return response()->json([
            'success' => true,
            'message' => 'API Token deleted successfully',
            'data' => $apiToken
        ]);
    }
}
