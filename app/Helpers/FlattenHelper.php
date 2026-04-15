<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class FlattenHelper
{
    public static function flatten($data, $config = [])
    {
        return collect($data)->map(function ($item) use ($config) {

            $row = $item->getAttributes();

            // 🔥 FILTER ROOT ONLY
            if (!empty($config['only_fields'])) {
                $row = array_intersect_key(
                    $row,
                    array_flip($config['only_fields'])
                );
            }

            // proses semua relasi
            foreach ($item->getRelations() as $relation => $relValue) {

                if (!$relValue) continue;

                $relConfig = $config['only'][$relation] ?? [];

                self::flattenRelation(
                    $relValue,
                    $relConfig,
                    $row,
                    $config,
                    $relation
                );
            }

            // 🔥 GLOBAL HIDDEN
            foreach (($config['hidden'] ?? []) as $hidden) {
                unset($row[$hidden]);
            }

            return $row;
        });
    }

    private static function flattenRelation($relValue, $relConfig, &$row, $globalConfig, $prefix)
    {
        if (!$relValue) return;

        $only = $relConfig['only'] ?? null;
        $except = $relConfig['except'] ?? [];
        $alias = $relConfig['alias'] ?? [];
        $children = $relConfig['children'] ?? [];

        // =========================================
        // 🔥 OBJECT RELATION
        // =========================================
        if (is_object($relValue)) {

            $attributes = $relValue->getAttributes();

            foreach ($attributes as $key => $val) {

                if ($val === null) continue;

                // ❌ hidden global
                if (in_array($key, $globalConfig['hidden'] ?? [])) continue;

                // ❌ except
                if (!empty($except) && in_array($key, $except)) continue;

                // ❌ only
                if ($only && $only !== '*') {
                    if (!in_array($key, $only)) continue;
                }

                $row[self::resolveKey($globalConfig, $prefix, $key, $alias)] = $val;
            }

            // =========================================
            // 🔥 RECURSIVE CHILDREN
            // =========================================
            foreach ($children as $childRelation => $childConfig) {

                $childValue = data_get($relValue, $childRelation);

                if (!$childValue) continue;

                self::flattenRelation(
                    $childValue,
                    $childConfig,
                    $row,
                    $globalConfig,
                    $prefix . '_' . $childRelation
                );
            }
        }

        // =========================================
        // 🔥 COLLECTION RELATION
        // =========================================
        if ($relValue instanceof \Illuminate\Support\Collection) {

            $row[$prefix] = $relValue->map(function ($item) {
                return $item->getAttributes();
            })->toArray();
        }
    }

    private static function resolveKey($config, $relation, $key, $alias = [])
    {
        return $alias[$key]
            ?? $config['alias'][$relation][$key] ?? null
            ?? Str::snake($relation . '_' . $key);
    }
}
