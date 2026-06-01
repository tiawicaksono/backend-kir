<?php

namespace App\Http\Controllers;

use App\Helpers\FlattenHelper;
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

        $query = $model::with('roles');
        $query->whereHas('roles', function ($q) {
            $q->where('is_active', true);
        });
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
                'email',
                'phone',
            ],

            'only' => [
                'roles' => [
                    'only' => [
                        'id',
                        'name',
                    ],
                ],
            ],

            'labels' => [
                'name' => 'Nama',
                'email' => 'Email',
                'phone' => 'No. HP',
                'roles.name' => 'Role',
            ],

            'searchable' => [
                [
                    'field' => 'name',
                    'label' => 'Nama',
                ],
                [
                    'field' => 'email',
                    'label' => 'Email',
                ],
                [
                    'field' => 'phone',
                    'label' => 'No. HP',
                ],
            ],

            'sortable' => [
                'name',
                'email',
                'phone',
            ],

            'hidden' => [
                'password',
                'remember_token',
                'email_verified_at',
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
        // ✅ VALIDASI
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:m_users,email',
            'phone' => 'required|string|max:20|unique:m_users,phone',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:m_roles,id',
        ]);

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
     * Update the specified resource in storage.
     */
    public function updateRole(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // ✅ VALIDASI
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:m_users,email,' . $id,
            'phone' => 'required|string|max:20|unique:m_users,phone,' . $id,
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
            'phone' => 'required|string|max:20|unique:m_users,phone,' . $id,
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
