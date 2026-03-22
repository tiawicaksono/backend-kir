<?php

namespace App\Http\Controllers;

use App\Models\RoleUser;
use App\Models\User;
use App\Services\QueryFilterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $model = User::class;
        $query = $model::query();
        $config = $this->getTableConfig();

        QueryFilterService::apply($query, $request, $model, $config);
        $perPage = $request->limit ?? 10;
        $result = $query->paginate($perPage);
        return response()->json([
            'data' => $result->items(),
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
            'foreign_keys' => [],
            'labels' => [],
            'hidden' => ['id', 'email_verified_at', 'remember_token', 'password', 'created_at', 'updated_at'],
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // Insert ke user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt('Password123!')
            ]);

            // Insert ke role_user (pivot)
            if ($request->filled('role')) {
                $roles = collect($request->role)
                    ->mapWithKeys(fn($id) => [$id => ['is_active' => true]])
                    ->toArray();

                $user->roles()->syncWithoutDetaching($roles);
            }

            DB::commit();

            return response()->json([
                'message' => 'User berhasil dibuat',
                'data' => $user
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Gagal membuat user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }
    /**
     * Update the specified resource in storage.
     */
    public function updateRole(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $inputRoles = collect($request->role); // contoh: [2,3]

        // Ambil semua role user saat ini
        $existingRoles = $user->roles()->pluck('role_id');

        // 1. Aktifkan / insert role yang dikirim
        foreach ($inputRoles as $roleId) {
            $user->roles()->syncWithoutDetaching([
                $roleId => ['is_active' => true]
            ]);
        }

        // 2. Nonaktifkan role yang tidak dikirim
        $rolesToDeactivate = $existingRoles->diff($inputRoles);

        if ($rolesToDeactivate->isNotEmpty()) {
            foreach ($rolesToDeactivate as $roleId) {
                $user->roles()->updateExistingPivot($roleId, [
                    'is_active' => false
                ]);
            }
        }

        return response()->json([
            'message' => 'Roles updated successfully'
        ]);
    }

    public function updateProfile(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:m_users,email,' . $id,
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if ($request->filled('password')) {
            $user->update([
                'password' => $request->password
            ]);
        }

        return response()->json([
            'message' => 'User updated',
            'data' => $user
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
