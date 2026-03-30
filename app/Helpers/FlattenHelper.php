<?php

namespace App\Helpers;

class FlattenHelper
{
    public static function flatten($data, $config)
    {
        return collect($data)->map(function ($item) use ($config) {
            $row = $item->toArray();

            if (!empty($config['relations'])) {
                foreach ($config['relations'] as $relName => $relConfig) {

                    foreach ($relConfig['columns'] as $col) {

                        // 🔥 ambil nested value
                        $value = $item;

                        foreach (explode('.', $relName) as $relation) {
                            $value = optional($value)->{$relation};
                        }

                        $row[$col] = optional($value)->{$col};
                    }

                    // 🔥 HAPUS RELASI (support nested)
                    $topRelation = explode('.', $relName)[0];
                    unset($row[$topRelation]);
                }
            }

            // 🔥 HIDDEN
            if (!empty($config['hidden'])) {
                foreach ($config['hidden'] as $hidden) {
                    unset($row[$hidden]);
                }
            }

            return $row;
        });
    }
}
