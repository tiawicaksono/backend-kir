<?php

namespace App\Http\Middleware;

use App\Models\Menu;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMenuAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        // sementara hardcode dulu
        // $userId = 3; // nanti ganti auth()->id()
        $user = $request->user();


        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $userId = $user->id;

        $path = '/' . ltrim(str_replace('api/', '', $request->path()), '/');
        // contoh: /loket/pembayaran
        $table = (new Menu)->getTable();
        $allowed = Menu::query()
            ->join('m_user_menus', "$table.id", '=', 'm_user_menus.menu_id')
            ->where('m_user_menus.user_id', $userId)
            ->where(function ($q) use ($path, $table) {
                $q->where("$table.route", $path)
                    ->orWhereIn("$table.parent_id", function ($sub) use ($path, $table) {
                        $sub->select('id')
                            ->from($table)
                            ->where('route', $path);
                    });
            })
            ->exists();

        if (! $allowed) {
            return response()->json([
                'message' => 'Forbidden'
            ], 403);
        }

        return $next($request);
    }
}
