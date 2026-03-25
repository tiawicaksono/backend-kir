<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Services\QueryFilterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserManagementController extends Controller
{
    public function counts()
    {
        return response()->json([
            'user' => User::count(),
            'role' => Role::count(),
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $model = User::class;

        // load roles
        $query = $model::with('roles');

        $config = $this->getTableConfig();

        QueryFilterService::apply($query, $request, $model, $config);

        $perPage = $request->limit ?? 10;
        $sortBy = $request->sort_by ?? 'id';
        $sortDir = $request->sort_dir ?? 'desc';

        $query->orderBy($sortBy, $sortDir);

        $result = $query->paginate($perPage);

        // 🔥 transform data biar clean
        $data = collect($result->items())->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'email' => $item->email,
                'email_verified_at' => $item->email_verified_at,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'phone' => $item->phone,

                // roles clean (tanpa pivot, tanpa field lain)
                'roles' => $item->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'is_active' => $role->pivot->is_active
                    ];
                })->values(),
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
            'foreign_keys' => [],
            'labels' => [],
            'searchable' => ['name', 'email'],
            'sortable' => ['name', 'email'],
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
                'phone' => $request->phone,
                'password' => bcrypt('Password123!')
            ]);

            // Insert ke role_user (pivot)
            if ($request->filled('roles')) {
                $roles = collect($request->roles)
                    ->mapWithKeys(fn($id) => [$id => ['is_active' => true]])
                    ->toArray();

                $user->roles()->syncWithoutDetaching($roles);
            }

            DB::commit();

            return response()->json([
                'message' => 'User berhasil dibuat',
                'data' => $this->transformUser($user->load('roles'))
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

        // ✅ VALIDASI
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:m_users,email,' . $id,
            'roles' => 'nullable|array',
            'roles.*' => 'exists:m_roles,id',
        ]);

        // ✅ UPDATE PROFILE
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        if ($request->filled('password')) {
            $user->update([
                'password' => bcrypt($request->password) // 🔥 jangan lupa hash
            ]);
        }

        // ✅ UPDATE ROLE (pakai logic kamu)
        if ($request->has('roles')) {
            $inputRoles = collect($request->roles); // 🔥 fix dari 'role' → 'roles'

            $existingRoles = $user->roles()->pluck('role_id');

            // 1. Aktifkan / insert role yang dikirim
            foreach ($inputRoles as $roleId) {
                $user->roles()->syncWithoutDetaching([
                    $roleId => ['is_active' => true]
                ]);
            }

            // 2. Nonaktifkan role yang tidak dikirim
            $rolesToDeactivate = $existingRoles->diff($inputRoles);

            foreach ($rolesToDeactivate as $roleId) {
                $user->roles()->updateExistingPivot($roleId, [
                    'is_active' => false
                ]);
            }
        }

        return response()->json([
            'message' => 'User updated successfully',
            'data' => $this->transformUser($user->load('roles'))
        ]);
    }

    private function transformUser($user)
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'phone' => $user->phone,
            'roles' => $user->roles->map(fn($role) => [
                'id' => $role->id,
                'name' => $role->name,
                'is_active' => $role->pivot->is_active
            ])->values(),
        ];
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
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }
}
