<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class QueryFilterService
{
    public static function apply(Builder $query, $request, $model, $config = [])
    {
        // 🔥 ROOT TABLE (AMAN)
        $table = $config['table']
            ?? (new $model)->getTable();

        // 🔥 ambil kolom valid
        $schema = config('database.connections.pgsql.schema', 'public');

        $validColumns = cache()->remember("columns_{$schema}_$table", 3600, function () use ($table, $schema) {
            return Schema::getColumnListing($schema . '.' . $table);
        });
        // dd($table, $validColumns);

        /**
         * =========================================================
         * 🔍 SEARCH
         * =========================================================
         */
        if ($request->filled('search')) {
            $keyword = strtolower(trim($request->search));

            if ($request->filled('search_by')) {
                $col = $request->search_by;

                // 🔹 kolom utama
                if (in_array($col, $validColumns)) {
                    $query->whereRaw(
                        "LOWER(CAST($table.$col AS TEXT)) LIKE ?",
                        ["%{$keyword}%"]
                    );
                }

                // 🔹 relasi
                if (!empty($config['relations'])) {
                    foreach ($config['relations'] as $relation => $relConfig) {

                        if (in_array($col, $relConfig['columns'] ?? [])) {
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

            // 🔥 GLOBAL SEARCH
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
                $query->whereIn("$table.$key", $value);
            } else {
                $query->where("$table.$key", $value);
            }
        }

        /**
         * =========================================================
         * 🔥 SORT (SUPPORT RELATION)
         * =========================================================
         */
        if ($request->filled('sort_by')) {

            $sortBy = $request->sort_by;
            $sortDir = $request->sort_order ?? 'asc';

            $isSorted = false;

            // 🔹 kolom utama
            if (in_array($sortBy, $validColumns)) {
                $query->orderBy("$table.$sortBy", $sortDir);
                $isSorted = true;
            }

            // 🔹 relasi
            if (!$isSorted && !empty($config['relations'])) {
                foreach ($config['relations'] as $relation => $relConfig) {

                    if (in_array($sortBy, $relConfig['columns'] ?? [])) {

                        $relations = explode('.', $relation);
                        $currentTable = $table;
                        $previousKey = null;

                        foreach ($relations as $index => $rel) {

                            $relConf = $config['relations'][implode('.', array_slice($relations, 0, $index + 1))] ?? null;

                            if (!$relConf) continue;

                            $relTable = $relConf['table']
                                ?? (isset($relConf['model'])
                                    ? (new $relConf['model'])->getTable()
                                    : null);

                            if (!$relTable) continue;

                            $foreignKey = $relConf['foreign_key'];
                            $ownerKey = $relConf['owner_key'];

                            // 🔥 JOIN BERANTAI
                            $query->leftJoin(
                                $relTable,
                                "$currentTable.$foreignKey",
                                '=',
                                "$relTable.$ownerKey"
                            );

                            $currentTable = $relTable;
                        }

                        // 🔥 FINAL SORT
                        $query->orderBy("$currentTable.$sortBy", $sortDir)
                            ->select("$table.*");

                        break;
                    }
                }
            }

            // 🔹 default fallback (kalau gak ketemu)
            if (!$isSorted) {
                $query->orderBy("$table.id", 'desc');
            }
        }

        return $query;
    }
}
