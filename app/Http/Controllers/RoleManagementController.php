<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Services\QueryFilterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $model = Role::class;
        $query = $model::query();
        $config = $this->getTableConfig();

        QueryFilterService::apply($query, $request, $model, $config);

        $perPage = $request->limit ?? 10;
        $result = $query->paginate($perPage);

        // 🔥 transform data biar clean
        $data = collect($result->items())->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $result->currentPage(),
                'per_page' => $result->perPage(),
                'total' => $result->total(),
            ],
            'config' => $config,
        ]);
    }

    public function getTableConfig()
    {
        return [
            'primary_key' => 'id',
            'labels' => [],
            'searchable' => ['name'],
            'sortable' => ['name'],
            'hidden' => ['id', 'created_at', 'updated_at'],
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:m_roles,name'
        ]);

        try {
            $role = Role::create([
                'name' => $validated['name']
            ]);

            return response()->json([
                'message' => 'Role berhasil dibuat',
                'data' => $role
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal membuat role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        // ✅ VALIDASI
        $request->validate([
            'name' => 'required|string|max:100|unique:m_roles,name,' . $id
        ]);

        $role->update([
            'name' => $request->name,
        ]);

        return response()->json([
            'message' => 'Role updated successfully',
            'data' => $role
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        return response()->json([
            'message' => 'Role deleted successfully'
        ]);
    }
}
