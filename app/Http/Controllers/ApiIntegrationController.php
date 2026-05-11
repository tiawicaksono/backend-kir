<?php

namespace App\Http\Controllers;

use App\Helpers\FlattenHelper;
use App\Models\ApiIntegration;
use App\Models\MasterArea;
use App\Models\MasterBahanBakar;
use App\Models\MasterJenisKendaraan;
use App\Models\MasterKelasJalan;
use App\Models\MasterMerk;
use App\Models\MasterMerkVarian;
use App\Models\MasterMerkVarianTipe;
use App\Models\MasterPegawai;
use App\Models\MasterStatusPenerbitan;
use App\Models\MasterSubJenisKendaraan;
use App\Services\QueryFilterService;
use Illuminate\Http\Request;

class ApiIntegrationController extends Controller
{
    public function index()
    {
        $data = ApiIntegration::with('lastTransaction')
            ->where('is_active', true)
            ->get();
        return response()->json($data);
    }

    public function show(Request $request, $prefix)
    {
        $modelMap = [
            'statuspenerbitan' => MasterStatusPenerbitan::class,
            'kelasjalan' => MasterKelasJalan::class,
            'area' => MasterArea::class,
            'fuel' => MasterBahanBakar::class,
            'pegawai' => MasterPegawai::class,
            'merk' => MasterMerk::class,
            'variantype' => MasterMerkVarian::class,
            'varian' => MasterMerkVarianTipe::class,
            'vehicletype' => MasterJenisKendaraan::class,
            'subvehicletype' => MasterSubJenisKendaraan::class,
        ];

        if (!isset($modelMap[$prefix])) {
            return response()->json(['message' => 'Invalid prefix'], 404);
        }

        $model = $modelMap[$prefix];
        $config = $this->getTableConfig($prefix);

        // 🔥 AUTO WITH dari only
        $with = $this->buildWithFromOnly($config['only'] ?? []);
        $query = $model::with($with);

        QueryFilterService::apply($query, $request, $model, $config);

        $result = $query->paginate($request->limit ?? 10);

        $data = FlattenHelper::flatten($result->items(), $config);

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $result->currentPage(),
                'per_page' => $result->perPage(),
                'total' => $result->total(),
            ],
            'config' => $config,
        ]);
    }

    /**
     * 🔥 AUTO BUILD WITH (nested)
     */
    private function buildWithFromOnly($only, $prefix = '')
    {
        $relations = [];

        foreach ($only as $relation => $conf) {

            $key = $prefix ? "$prefix.$relation" : $relation;
            $relations[] = $key;

            if (!empty($conf['children'])) {
                $relations = array_merge(
                    $relations,
                    $this->buildWithFromOnly($conf['children'], $key)
                );
            }
        }

        return $relations;
    }

    /**
     * 🔥 CONFIG
     */
    private function getTableConfig($prefix)
    {
        return [

            'statuspenerbitan' => [
                'primary_key' => 'issuance_id',
                'labels' => [
                    'issuance_id' => 'ID',
                    'issuance_code' => 'Kode',
                    'issuance_name' => 'Nama',
                    'issuance_desc' => 'Deskripsi',
                ],
                'searchable' => [
                    [
                        'field' => 'issuance_name',
                        'label' => 'Nama'
                    ],
                    [
                        'field' => 'issuance_code',
                        'label' => 'Kode'
                    ],
                    [
                        'field' => 'issuance_desc',
                        'label' => 'Deskripsi'
                    ]
                ],
                'hidden' => ['created_at', 'updated_at'],
            ],

            'kelasjalan' => [
                'primary_key' => 'kelasjalan_id',
                'labels' => [
                    'kelasjalan_id' => 'ID',
                    'kelasjalan_code' => 'Kode',
                    'kelasjalan_name' => 'Nama',
                    'kelasjalan_desc' => 'Deskripsi',
                ],
                'searchable' => [
                    [
                        'field' => 'kelasjalan_code',
                        'label' => 'Kode'
                    ],
                    [
                        'field' => 'kelasjalan_name',
                        'label' => 'Nama'
                    ]
                ],
                'hidden' => ['created_at', 'updated_at'],
            ],

            'area' => [
                'primary_key' => 'area_id',
                'only_fields' => ['area_id', 'area_code', 'area_name'],
                'labels' => [
                    'area_id' => 'ID',
                    'area_code' => 'Kode',
                    'area_name' => 'Nama',
                ],
                'searchable' => [
                    [
                        'field' => 'area_code',
                        'label' => 'Kode'
                    ],
                    [
                        'field' => 'area_name',
                        'label' => 'Nama'
                    ]
                ],
                // 'hidden' => ['created_at', 'updated_at'],
            ],

            'fuel' => [
                'primary_key' => 'fuel_id',
                'labels' => [
                    'fuel_id' => 'ID',
                    'fuel_name' => 'Nama',
                ],
                'searchable' => [
                    [
                        'field' => 'fuel_name',
                        'label' => 'Nama'
                    ]
                ],
                'hidden' => ['created_at', 'updated_at'],
            ],

            'pegawai' => [
                'primary_key' => 'user_id',
                'only_fields' => ['full_name', 'pangkat', 'identity_number', 'user_id', 'job_name'],
                'labels' => [
                    'user_id' => 'ID',
                    'full_name' => 'Nama',
                    'job_name' => 'Jabatan',
                    'pangkat' => 'Pangkat',
                    'identity_number' => 'NIP',
                ],
                'searchable' => [
                    [
                        'field' => 'full_name',
                        'label' => 'Nama'
                    ],
                    [
                        'field' => 'job_name',
                        'label' => 'Jabatan'
                    ]
                ],
                // 'hidden' => ['created_at', 'updated_at'],
            ],

            'merk' => [
                'primary_key' => 'vehicle_brand_id',
                'labels' => [
                    'vehicle_brand_id' => 'ID',
                    'vehicle_brand_name' => 'Nama',
                ],
                'searchable' => [
                    [
                        'field' => 'vehicle_brand_name',
                        'label' => 'Nama'
                    ]
                ],
                'hidden' => ['created_at', 'updated_at'],
            ],

            'variantype' => [
                'primary_key' => 'vehicle_varian_type_id',

                'only' => [
                    'merk' => [
                        'only' => ['vehicle_brand_name'],
                        'alias' => ['vehicle_brand_name' => 'vehicle_brand_name']
                    ]
                ],

                'labels' => [
                    'vehicle_varian_type_id' => 'ID',
                    'vehicle_varian_type_name' => 'Nama',
                    'vehicle_brand_name' => 'Merk',
                ],

                'searchable' => [
                    [
                        'field' => 'vehicle_varian_type_name',
                        'label' => 'Nama'
                    ],
                    [
                        'field' => 'merk.vehicle_brand_name',
                        'label' => 'Merk'
                    ]
                ],

                'hidden' => ['vehicle_brand_id', 'created_at', 'updated_at'],
            ],

            'varian' => [
                'primary_key' => 'vehicle_varian_id',

                'only' => [
                    'varian' => [
                        'only' => ['vehicle_varian_type_name'],
                        'alias' => ['vehicle_varian_type_name' => 'vehicle_varian_type_name'],
                        'children' => [
                            'merk' => [
                                'only' => ['vehicle_brand_name'],
                                'alias' => ['vehicle_brand_name' => 'vehicle_brand_name']
                            ]
                        ]
                    ]
                ],

                'searchable' => [
                    [
                        'field' => 'vehicle_varian_name',
                        'label' => 'Nama'
                    ],
                    [
                        'field' => 'varian.vehicle_varian_type_name',
                        'label' => 'Tipe Varians'
                    ],
                    [
                        'field' => 'varian.merk.vehicle_brand_name',
                        'label' => 'Merk'
                    ]
                ],

                'hidden' => ['created_at', 'updated_at'],
            ],

            'vehicletype' => [
                'primary_key' => 'vehicle_type_id',
                'labels' => [
                    'vehicle_type_id' => 'ID',
                    'vehicle_type_name' => 'Nama',
                ],
                'searchable' => [
                    [
                        'field' => 'vehicle_type_name',
                        'label' => 'Nama'
                    ]
                ],
                'hidden' => ['created_at', 'updated_at'],
            ],

            'subvehicletype' => [
                'primary_key' => 'vehicle_sub_id',
                'only' => [
                    'masterJenisKendaraan' => [
                        'only' => ['vehicle_type_name'],
                        'alias' => ['vehicle_type_name' => 'vehicle_type_name']
                    ]
                ],
                'labels' => [
                    'vehicle_sub_id' => 'ID',
                    'vehicle_sub_name' => 'Nama',
                ],
                'searchable' => [
                    [
                        'field' => 'vehicle_sub_name',
                        'label' => 'Nama'
                    ],
                    [
                        'field' => 'masterJenisKendaraan.vehicle_type_name',
                        'label' => 'Jenis Kendaraan'
                    ]
                ],
                'hidden' => ['created_at', 'updated_at'],
            ],

        ][$prefix] ?? [
            'primary_key' => 'id',
            'hidden' => ['created_at', 'updated_at'],
        ];
    }
}
