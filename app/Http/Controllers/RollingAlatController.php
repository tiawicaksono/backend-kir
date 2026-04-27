<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RollingAlatController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->get('from');
        $to = $request->get('to');
        $gedung = $request->get('gedung_uji');

        $users = User::whereHas('roles', function ($q) use ($gedung) {
            $q->where('m_roles.id', 3)
                ->where('m_role_users.is_active', true);

            if ($gedung) {
                $q->where('m_role_users.gedung_uji', $gedung);
            }
        })
            ->with(['roles' => function ($q) use ($gedung) {
                $q->where('m_roles.id', 3);

                if ($gedung) {
                    $q->where('m_role_users.gedung_uji', $gedung);
                }
            }])
            ->get();

        return $users->map(function ($user) {
            $role = $user->roles->first();
            $pivot = $role?->pivot;

            return [
                'key' => $user->id,
                'title' => $user->name,
                'gedung_uji' => $pivot?->gedung_uji,

                'direction' => $pivot?->gedung_uji == 2 ? 'right' : 'left',

                'prauji' => $pivot?->prauji ?? false,
                'emisi' => $pivot?->emisi ?? false,
                'lampu' => $pivot?->lampu ?? false,
                'pitlift' => $pivot?->pitlift ?? false,
                'rem' => $pivot?->rem ?? false,
            ];
        });
    }

    public function store(Request $request)
    {
        $request->validate([
            'data' => 'required|array',
            'data.*.user_id' => 'required|exists:m_users,id',
            'data.*.gedung_uji' => 'required|integer',
        ]);

        DB::transaction(function () use ($request) {

            foreach ($request->data as $item) {

                DB::table('m_role_users')
                    ->where('user_id', $item['user_id'])
                    ->where('role_id', 3)
                    ->update([
                        'gedung_uji' => $item['gedung_uji'],
                        'prauji' => $item['prauji'] ?? 0,
                        'emisi' => $item['emisi'] ?? 0,
                        'lampu' => $item['lampu'] ?? 0,
                        'pitlift' => $item['pitlift'] ?? 0,
                        'rem' => $item['rem'] ?? 0,
                        'updated_at' => now(),
                    ]);
            }
        });

        return response()->json([
            'message' => 'Berhasil update rolling alat'
        ]);
    }
}
