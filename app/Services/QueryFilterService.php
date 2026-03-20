<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class QueryFilterService
{
    public static function apply(Builder $query, $request, $model)
    {
        $table = (new $model)->getTable();
        $validColumns = Schema::getColumnListing($table);
        Log::info([
            'search_by' => $request->search_by,
            'validColumns' => $validColumns
        ]);
        // 🔍 SEARCH
        if ($request->search) {
            $searchBy = $request->search_by;

            $keyword = strtolower($request->search);

            $query->where(function ($q) use ($validColumns, $searchBy, $keyword) {

                if ($searchBy && in_array($searchBy, $validColumns)) {
                    $q->whereRaw(
                        "LOWER(CAST($searchBy AS TEXT)) LIKE ?",
                        ["%{$keyword}%"]
                    );
                } else {
                    foreach ($validColumns as $col) {
                        if (in_array($col, ['created_at', 'updated_at'])) continue;

                        $q->orWhereRaw(
                            "LOWER(CAST($col AS TEXT)) LIKE ?",
                            ["%{$keyword}%"]
                        );
                    }
                }
            });
        }

        // 🔥 SORT
        if ($request->sort_by && in_array($request->sort_by, $validColumns)) {
            $query->orderBy(
                $request->sort_by,
                $request->sort_order ?? 'asc'
            );
        }

        return $query;
    }
}
