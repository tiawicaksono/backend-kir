<?php

namespace App\Helpers;

class FlattenHelper
{
    public static function flatten($data, $config)
    {
        return collect($data)->map(function ($item) use ($config) {
            $row = $item->toArray();

            // 🔥 HANDLE RELATIONS
            if (!empty($config['relations'])) {
                foreach ($config['relations'] as $relName => $relConfig) {

                    if (!isset($row[$relName])) continue;

                    foreach ($relConfig['columns'] as $col) {
                        $row[$col] = $row[$relName][$col] ?? null;
                    }

                    // 🔥 hapus nested object
                    unset($row[$relName]);
                }
            }

            return $row;
        });
    }
}
