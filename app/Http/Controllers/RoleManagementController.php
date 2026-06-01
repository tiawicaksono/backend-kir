<?php

namespace App\Http\Controllers;

use App\Helpers\FlattenHelper;
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

        // default sort
        $primaryKey = $config['primary_key'] ?? null;

        if (!$request->filled('sort_by') && $primaryKey) {
            $request->merge([
                'sort_by' => $primaryKey,
                'sort_order' => 'desc',
            ]);
        }

        QueryFilterService::apply($query, $request, $model, $config);

        $perPage = $request->limit ?? 10;
        $result = $query->paginate($perPage);

        $data = FlattenHelper::flatten($result->items(), $config);

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

    private function getTableConfig()
    {
        return [
            'primary_key' => 'id',

            'only_fields' => [
                'id',
                'name',
            ],

            'labels' => [
                'name' => 'Name',
            ],

            'searchable' => [
                [
                    'field' => 'name',
                    'label' => 'Name',
                ],
            ],

            'sortable' => [
                'name',
            ],

            'hidden' => [
                'created_at',
                'updated_at',
            ],
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
