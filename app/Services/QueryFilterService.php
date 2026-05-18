<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class QueryFilterService
{
    private static function normalize($value)
    {
        return strtolower(str_replace(' ', '', trim($value)));
    }

    public static function apply(Builder $query, $request, $model, $config = [])
    {
        // 🔥 ROOT TABLE
        $table = $config['table'] ?? (new $model)->getTable();

        // 🔥 ambil kolom valid
        $schema = config('database.connections.pgsql.schema', 'public');

        $validColumns = cache()->remember("columns_{$schema}_$table", 3600, function () use ($table, $schema) {
            return Schema::getColumnListing($schema . '.' . $table);
        });

        /**
         * =========================================================
         * 🔍 SEARCH (SUPPORT DOT NOTATION + RELATION)
         * =========================================================
         */
        if ($request->filled('search')) {

            $keyword = self::normalize($request->search);

            // 🔥 pakai searchable kalau ada
            if (!empty($config['searchable'])) {

                $query->where(function ($q) use ($config, $keyword, $table, $request) {

                    $q->whereRaw('1=0');

                    /**
                     * =========================================
                     * 🔥 FILTER SEARCHABLE BY search_by
                     * =========================================
                     */
                    $searchables = $config['searchable'];

                    if ($request->filled('search_by')) {

                        $searchables = array_filter(
                            $searchables,
                            function ($item) use ($request) {

                                $field = is_array($item)
                                    ? ($item['field'] ?? null)
                                    : $item;

                                return $field === $request->search_by;
                            }
                        );
                    }

                    /**
                     * =========================================
                     * 🔥 LOOP SEARCHABLE
                     * =========================================
                     */
                    foreach ($searchables as $item) {

                        /**
                         * support:
                         * - string
                         * - object config
                         */
                        $field = is_array($item)
                            ? ($item['field'] ?? null)
                            : $item;

                        if (!$field) continue;

                        $field = trim($field);

                        /**
                         * =====================================
                         * 🔥 RELATION SEARCH
                         * =====================================
                         */
                        if (str_contains($field, '.')) {

                            self::applyWhereHas(
                                $q,
                                $field,
                                $keyword
                            );
                        } else {

                            /**
                             * =================================
                             * 🔥 ROOT COLUMN SEARCH
                             * =================================
                             */
                            $q->orWhereRaw(
                                "REPLACE(LOWER(CAST($table.$field AS TEXT)), ' ', '') LIKE ?",
                                ["%{$keyword}%"]
                            );
                        }
                    }
                });
            } else {

                /**
                 * =========================================
                 * 🔙 FALLBACK SEARCH
                 * =========================================
                 */
                $query->where(function ($q) use ($validColumns, $keyword, $table) {

                    $q->whereRaw('1=0');

                    foreach ($validColumns as $col) {

                        if (in_array($col, ['created_at', 'updated_at'])) {
                            continue;
                        }

                        $q->orWhereRaw(
                            "REPLACE(LOWER(CAST($table.$col AS TEXT)), ' ', '') LIKE ?",
                            ["%{$keyword}%"]
                        );
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
                $query->whereIn(
                    DB::raw("REPLACE(LOWER($table.$key), ' ', '')"),
                    array_map(fn($v) => self::normalize($v), $value)
                );
            } else {
                $query->whereRaw(
                    "REPLACE(LOWER($table.$key), ' ', '') = ?",
                    [self::normalize($value)]
                );
            }
        }

        /**
         * =========================================================
         * 🔥 SORT (BASIC)
         * =========================================================
         */
        if ($request->filled('sort_by')) {

            $sortBy = $request->sort_by;
            $sortDir = $request->sort_order ?? 'asc';

            // 🔹 kolom utama
            if (in_array($sortBy, $validColumns)) {
                $query->orderBy("$table.$sortBy", $sortDir);
            }
        }

        return $query;
    }

    /**
     * =========================================================
     * 🔥 HANDLE RELATION SEARCH (DOT NOTATION)
     * =========================================================
     */
    private static function applyWhereHas($query, $relationPath, $keyword)
    {
        $parts = explode('.', $relationPath);

        $column = array_pop($parts);     // nama_kota
        $relation = implode('.', $parts); // kota / kota.provinsi

        $query->orWhereHas($relation, function ($q) use ($column, $keyword) {

            $q->whereRaw(
                "REPLACE(LOWER(CAST($column AS TEXT)), ' ', '') LIKE ?",
                ["%{$keyword}%"]
            );
        });
    }
}
