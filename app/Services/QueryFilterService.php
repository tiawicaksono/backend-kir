<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class QueryFilterService
{
    public static function apply(Builder $query, $request, $model, $config = [])
    {
        $table = (new $model)->getTable();

        // 🔥 ambil kolom valid
        $validColumns = cache()->remember("columns_$table", 3600, function () use ($table) {
            return Schema::getColumnListing($table);
        });

        /**
         * =========================================================
         * 🔍 SEARCH
         * =========================================================
         */
        if ($request->filled('search')) {
            $keyword = strtolower(trim($request->search));

            // 🔥 SEARCH BY
            if ($request->filled('search_by')) {
                $col = $request->search_by;

                // kolom utama
                if (in_array($col, $validColumns)) {
                    $query->whereRaw(
                        "LOWER(CAST($col AS TEXT)) LIKE ?",
                        ["%{$keyword}%"]
                    );
                }

                // relasi
                if (!empty($config['relations'])) {
                    foreach ($config['relations'] as $relation => $relConfig) {
                        if (in_array($col, $relConfig['columns'])) {
                            $query->orWhereHas($relation, function ($q) use ($col, $keyword) {
                                $q->whereRaw(
                                    "LOWER(CAST($col AS TEXT)) LIKE ?",
                                    ["%{$keyword}%"]
                                );
                            });
                        }
                    }
                }
            }

            // 🔥 GLOBAL SEARCH (FIX TOTAL)
            else {
                $query->where(function ($q) use ($validColumns, $keyword, $config) {

                    $conditions = [];
                    $bindings = [];

                    // 🔹 kolom utama
                    foreach ($validColumns as $col) {
                        if (in_array($col, ['created_at', 'updated_at'])) continue;

                        $conditions[] = "LOWER(CAST($col AS TEXT)) LIKE ?";
                        $bindings[] = "%{$keyword}%";
                    }

                    if (!empty($conditions)) {
                        $q->whereRaw("(" . implode(" OR ", $conditions) . ")", $bindings);
                    }

                    // 🔹 relasi
                    if (!empty($config['relations'])) {
                        foreach ($config['relations'] as $relation => $relConfig) {
                            $q->orWhereHas($relation, function ($relQ) use ($keyword, $relConfig) {

                                $relConditions = [];
                                $relBindings = [];

                                foreach ($relConfig['columns'] as $col) {
                                    $relConditions[] = "LOWER(CAST($col AS TEXT)) LIKE ?";
                                    $relBindings[] = "%{$keyword}%";
                                }

                                if (!empty($relConditions)) {
                                    $relQ->whereRaw("(" . implode(" OR ", $relConditions) . ")", $relBindings);
                                }
                            });
                        }
                    }
                });
            }
        }

        /**
         * =========================================================
         * 🔥 FILTER BIASA
         * =========================================================
         */
        foreach ($request->all() as $key => $value) {

            if (in_array($key, [
                'page',
                'limit',
                'sort_by',
                'sort_order',
                'search',
                'search_by',
                'sorter',
                'or_filters'
            ])) continue;

            if (!in_array($key, $validColumns)) continue;

            if (is_array($value)) {
                $query->whereIn($key, $value);
            } else {
                $query->where($key, $value);
            }
        }

        /**
         * =========================================================
         * 🔥 SORT
         * =========================================================
         */
        if ($request->filled('sort_by')) {
            if (in_array($request->sort_by, $validColumns)) {
                $query->orderBy(
                    $request->sort_by,
                    $request->sort_order ?? 'asc'
                );
            }
        }

        return $query;
    }
}
