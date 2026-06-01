<?php

namespace App\Helpers;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FlattenHelper
{
    public static function flatten($data, $config = [])
    {
        return collect($data)->map(function ($item) use ($config) {

            // =========================
            // ROOT DATA (SAFE MIX)
            // =========================
            $row = array_merge(
                $item->getAttributes(),
                $item->getRelations(),
                $item->toArray()
            );

            // =========================
            // FILTER ROOT FIELDS
            // =========================
            if (!empty($config['only_fields'])) {

                $filtered = [];

                foreach ($config['only_fields'] as $field) {
                    $filtered[$field] = data_get($row, $field, null);
                }

                $row = $filtered;
            }

            // =========================
            // PROCESS RELATIONS CONFIG
            // =========================
            foreach (($config['only'] ?? []) as $relation => $relConfig) {

                // skip root field config
                if (is_numeric($relation)) {
                    continue;
                }

                $relValue = data_get($item, $relation);

                self::flattenRelation(
                    $relValue,
                    $relConfig,
                    $row,
                    $config,
                    $relation
                );
            }

            // =========================
            // GLOBAL HIDDEN
            // =========================
            foreach (($config['hidden'] ?? []) as $hidden) {
                unset($row[$hidden]);
            }

            return $row;
        });
    }

    private static function flattenRelation(
        $relValue,
        $relConfig,
        &$row,
        $globalConfig,
        $prefix
    ) {
        $only = $relConfig['only'] ?? null;
        $except = $relConfig['except'] ?? [];
        $alias = $relConfig['alias'] ?? [];
        $children = $relConfig['children'] ?? [];

        // =========================
        // NULL RELATION
        // =========================
        if (is_null($relValue)) {

            // parent fields
            if ($only && $only !== '*') {

                foreach ($only as $key) {

                    $resolvedKey = self::resolveKey(
                        $globalConfig,
                        $prefix,
                        $key,
                        $alias
                    );

                    $row[$resolvedKey] = null;
                }
            }

            // children fields
            foreach ($children as $childRelation => $childConfig) {

                $childOnly = $childConfig['only'] ?? [];
                $childAlias = $childConfig['alias'] ?? [];

                foreach ($childOnly as $childKey) {

                    $resolvedKey = self::resolveKey(
                        $globalConfig,
                        $prefix . '_' . $childRelation,
                        $childKey,
                        $childAlias
                    );

                    $row[$resolvedKey] = null;
                }
            }

            return;
        }

        // =========================
        // MODEL OBJECT
        // =========================
        if ($relValue instanceof Model) {

            $attributes = array_merge(
                $relValue->getAttributes(),
                $relValue->toArray()
            );

            if ($only && $only !== '*') {

                foreach ($only as $key) {

                    if (in_array($key, $except)) continue;

                    if (in_array($key, $globalConfig['hidden'] ?? [])) continue;

                    $resolvedKey = self::resolveKey(
                        $globalConfig,
                        $prefix,
                        $key,
                        $alias
                    );

                    $row[$resolvedKey] = data_get($attributes, $key, null);
                }
            } else {

                foreach ($attributes as $key => $val) {

                    if (in_array($key, $except)) continue;

                    if (in_array($key, $globalConfig['hidden'] ?? [])) continue;

                    $resolvedKey = self::resolveKey(
                        $globalConfig,
                        $prefix,
                        $key,
                        $alias
                    );

                    $row[$resolvedKey] = $val;
                }
            }

            // =========================
            // CHILD RELATIONS
            // =========================
            foreach ($children as $childRelation => $childConfig) {

                $childValue = data_get($relValue, $childRelation);

                self::flattenRelation(
                    $childValue,
                    $childConfig,
                    $row,
                    $globalConfig,
                    $prefix . '_' . $childRelation
                );
            }
        }

        // =========================
        // COLLECTION
        // =========================
        if ($relValue instanceof Collection) {

            // 🔥 NEW: support only untuk belongsToMany / hasMany
            if ($only && $only !== '*') {

                $row[$prefix] = $relValue->map(function ($item) use (
                    $only,
                    $except,
                    $globalConfig
                ) {

                    $attributes = $item instanceof Model
                        ? array_merge(
                            $item->getAttributes(),
                            $item->toArray()
                        )
                        : (array) $item;

                    $filtered = [];

                    foreach ($only as $field) {

                        if (in_array($field, $except)) {
                            continue;
                        }

                        if (in_array($field, $globalConfig['hidden'] ?? [])) {
                            continue;
                        }

                        $filtered[$field] = data_get(
                            $attributes,
                            $field,
                            null
                        );
                    }

                    return $filtered;
                })->toArray();

                return;
            }

            // 🔥 EXISTING BEHAVIOUR (tetap sama)
            $row[$prefix] = $relValue->map(function ($item) {
                return $item instanceof Model
                    ? array_merge(
                        $item->getAttributes(),
                        $item->toArray()
                    )
                    : (array) $item;
            })->toArray();

            return;
        }

        // =========================
        // ARRAY FALLBACK
        // =========================
        if (is_array($relValue)) {
            $row[$prefix] = $relValue;
        }
    }

    private static function resolveKey(
        $config,
        $relation,
        $key,
        $alias = []
    ) {
        return $alias[$key]
            ?? $config['alias'][$relation][$key] ?? null
            ?? Str::snake($relation . '_' . $key);
    }
}
