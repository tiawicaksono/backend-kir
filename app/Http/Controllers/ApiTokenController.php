<?php

namespace App\Http\Controllers;

use App\Models\ApiToken;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ApiTokenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(ApiToken::all());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url_api' => 'required|string|max:255',
            'token' => 'required|string',
        ]);

        $apiToken = ApiToken::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'API Token created successfully',
            'data' => $apiToken
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
            'url_api' => 'required|string|max:255',
            'token' => 'required|string',
        ]);

        $apiToken->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'API Token updated successfully',
            'data' => $apiToken
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

        // ✅ Ambil data
        $apiToken = ApiToken::findOrFail($validated['id']);

        // ✅ Update
        $apiToken->is_active = $validated['is_active'];
        $apiToken->save();

        return response()->json([
            'success' => true,
            'message' => 'API Token status updated successfully',
            'data' => $apiToken
        ]);
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
