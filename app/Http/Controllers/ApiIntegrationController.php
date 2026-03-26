<?php

namespace App\Http\Controllers;

use App\Helpers\FlattenHelper;
use Illuminate\Support\Facades\Schema;
use App\Http\Resources\ApiIntegrationResource;
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
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = ApiIntegration::with('lastTransaction')->get();
        return response()->json(ApiIntegrationResource::collection($data));
    }

    /**
     * Display the specified resource.
     */
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

        // 🔥 WITH RELATIONS
        $query = $model::with(array_keys($config['relations'] ?? []));

        QueryFilterService::apply($query, $request, $model, $config);

        $perPage = $request->limit ?? 10;
        $sortBy = $request->sort_by ?? 'created_at';
        $sortDir = $request->sort_dir ?? 'desc';
        $query->orderBy($sortBy, $sortDir);
        $result = $query->paginate($perPage);

        // 🔥 FLATTEN DATA
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
     * return primary key and foreign keys
     */
    private function getTableConfig($prefix)
    {
        return [
            'statuspenerbitan' => [
                'primary_key' => 'issuance_id',
                'foreign_keys' => [],
                'labels' => [
                    'issuance_id' => 'ID',
                    'issuance_code' => 'Kode',
                    'issuance_name' => 'Nama',
                    'issuance_desc' => 'Deskripsi',
                ],
                'searchable' => ['issuance_code', 'issuance_name', 'issuance_desc'],
                'sortable' => ['issuance_id', 'issuance_code', 'issuance_name'],
                'hidden' => ['created_at', 'updated_at'],
            ],

            'kelasjalan' => [
                'primary_key' => 'kelasjalan_id',
                'foreign_keys' => [],
                'labels' => [
                    'kelasjalan_id' => 'ID',
                    'kelasjalan_code' => 'Kode',
                    'kelasjalan_name' => 'Nama',
                    'kelasjalan_desc' => 'Deskripsi',
                    'muatan_sumbu_terberat' => 'MST',
                    'vehicle_length' => 'Panjang',
                    'vehicle_height' => 'Tinggi',
                    'vehicle_width' => 'Lebar',
                ],
                'searchable' => ['kelasjalan_code', 'kelasjalan_name', 'kelasjalan_desc'],
                'sortable' => ['kelasjalan_id', 'kelasjalan_code', 'kelasjalan_name'],
                'hidden' => ['created_at', 'updated_at'],
            ],

            'area' => [
                'primary_key' => 'area_id',
                'foreign_keys' => [],
                'labels' => [
                    'area_id' => 'ID',
                    'area_code' => 'Kode',
                    'area_name' => 'Nama',
                    'area_address' => 'Alamat',
                ],
                'searchable' => ['area_code', 'area_name'],
                'sortable' => ['area_id', 'area_code', 'area_name'],
                'hidden' => ['area_email', 'area_pic', 'area_telp', 'area_active', 'area_logo_active', 'logo', 'logo_gray', 'created_at', 'updated_at'],
            ],

            'fuel' => [
                'primary_key' => 'fuel_id',
                'foreign_keys' => [],
                'labels' => [
                    'fuel_id' => 'ID',
                    'fuel_name' => 'Nama',
                    'fuel_desc' => 'Deskripsi',
                ],
                'searchable' => ['fuel_name', 'fuel_desc'],
                'sortable' => ['fuel_id', 'fuel_name'],
                'hidden' => ['created_at', 'updated_at'],
            ],

            'pegawai' => [
                'primary_key' => 'user_id',
                'foreign_keys' => [],
                'labels' => [
                    'user_id' => 'ID',
                    'job_name' => 'Jabatan',
                    'identity_number' => 'NIK/NIP',
                    'full_name' => 'Nama',
                    'pangkat' => 'Pangkat',
                    'email' => 'Email',
                    'phone_number' => 'No Telepon',
                    'address' => 'Alamat',
                ],
                'searchable' => ['full_name', 'job_name'],
                'sortable' => ['user_id', 'job_name', 'full_name'],
                'hidden' => ['job_type_id', 'job_type_code', 'job_type_name', 'job_id', 'job_code', 'sign_active', 'sign1', 'sign2', 'sign3', 'job_active', 'created_at', 'updated_at'],
            ],

            'merk' => [
                'primary_key' => 'vehicle_brand_id',
                'foreign_keys' => [],
                'labels' => [
                    'vehicle_brand_id' => 'ID',
                    'vehicle_brand_code' => 'Kode',
                    'vehicle_brand_name' => 'Nama',
                    'vehicle_brand_desc' => 'Deskripsi',
                ],
                'searchable' => ['vehicle_brand_code', 'vehicle_brand_name', 'vehicle_brand_desc'],
                'sortable' => ['vehicle_brand_id', 'vehicle_brand_code', 'vehicle_brand_name'],
                'hidden' => ['created_at', 'updated_at'],
            ],

            'variantype' => [
                'primary_key' => 'vehicle_varian_type_id',
                'foreign_keys' => ['vehicle_brand_id'],
                'relations' => [
                    'merk' => [
                        'model' => MasterMerk::class,
                        'foreign_key' => 'vehicle_brand_id',
                        'owner_key' => 'vehicle_brand_id',
                        'columns' => ['vehicle_brand_name']
                    ]
                ],
                'labels' => [
                    'vehicle_varian_type_id' => 'ID',
                    'vehicle_varian_type_code' => 'Kode',
                    'vehicle_varian_type_name' => 'Nama',
                    'vehicle_varian_type_desc' => 'Deskripsi',
                    'vehicle_brand_name' => 'Merk',
                ],
                // 'column_order' => [
                //     'vehicle_varian_type_id',
                //     'vehicle_varian_type_code',
                //     'vehicle_varian_type_name',
                //     'vehicle_brand_name',
                //     'vehicle_varian_type_desc',
                // ],
                'searchable' => ['vehicle_varian_type_code', 'vehicle_varian_type_name', 'vehicle_brand_name'],
                'sortable' => ['vehicle_varian_type_id', 'vehicle_varian_type_code', 'vehicle_varian_type_name', 'vehicle_brand_name'],
                'hidden' => ['vehicle_brand_id', 'created_at', 'updated_at'],
            ],

            'varian' => [
                'primary_key' => 'vehicle_varian_id',
                'foreign_keys' => ['vehicle_varian_type_id'],
                'relations' => [
                    'varian' => [
                        'model' => MasterMerkVarian::class,
                        'foreign_key' => 'vehicle_varian_type_id',
                        'owner_key' => 'vehicle_varian_type_id',
                        'columns' => ['vehicle_varian_type_name']
                    ]
                ],
                'labels' => [
                    'vehicle_varian_id' => 'ID',
                    'vehicle_varian_code' => 'Kode',
                    'vehicle_varian_name' => 'Nama',
                    'vehicle_varian_desc' => 'Deskripsi',
                ],
                'searchable' => ['vehicle_varian_code', 'vehicle_varian_name', 'vehicle_varian_desc'],
                'sortable' => ['vehicle_varian_id', 'vehicle_varian_code', 'vehicle_varian_name'],
                'hidden' => ['created_at', 'updated_at'],
            ],

            'vehicletype' => [
                'primary_key' => 'vehicle_type_id',
                'foreign_keys' => [],
                'labels' => [
                    'vehicle_type_id' => 'ID',
                    'vehicle_type_code' => 'Kode',
                    'vehicle_type_name' => 'Nama',
                    'vehicle_type_desc' => 'Deskripsi',
                ],
                'searchable' => ['vehicle_type_code', 'vehicle_type_name', 'vehicle_type_desc'],
                'sortable' => ['vehicle_type_id', 'vehicle_type_code', 'vehicle_type_name'],
                'hidden' => ['created_at', 'updated_at'],
            ],

            'subvehicletype' => [
                'primary_key' => 'vehicle_sub_id',
                'foreign_keys' => ['vehicle_type_id'],
                'labels' => [
                    'vehicle_sub_id' => 'ID',
                    'vehicle_sub_code' => 'Kode',
                    'vehicle_sub_name' => 'Nama',
                    'vehicle_sub_desc' => 'Deskripsi',
                ],
                'searchable' => ['vehicle_sub_code', 'vehicle_sub_name', 'vehicle_sub_desc'],
                'sortable' => ['vehicle_sub_id', 'vehicle_sub_code', 'vehicle_sub_name'],
                'hidden' => ['created_at', 'updated_at'],
            ],

        ][$prefix] ?? [
            'primary_key' => 'id',
            'foreign_keys' => [],
            'labels' => [],
            'hidden' => ['created_at', 'updated_at'],
        ];
    }
}
