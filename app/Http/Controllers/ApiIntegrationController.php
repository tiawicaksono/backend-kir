<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Schema;
use App\Http\Resources\ApiIntegrationResource;
use App\Models\ApiIntegration;
use App\Models\MasterArea;
use App\Models\MasterBahanBakar;
use App\Models\MasterKelasJalan;
use App\Models\MasterMerk;
use App\Models\MasterStatusPenerbitan;
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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
            'merk' => MasterMerk::class,
        ];

        if (!isset($modelMap[$prefix])) {
            return response()->json(['message' => 'Invalid prefix'], 404);
        }

        $model = $modelMap[$prefix];

        $query = $model::query();

        // 🔥 APPLY FILTER ENGINE
        QueryFilterService::apply($query, $request, $model);

        $perPage = $request->limit ?? 10;
        $result = $query->paginate($perPage);

        return response()->json([
            'data' => $result->items(),
            'meta' => [
                'current_page' => $result->currentPage(),
                'per_page' => $result->perPage(),
                'total' => $result->total(),
            ],
            'config' => $this->getTableConfig($prefix),
        ]);
    }

    /**
     * return primary key and foreign keys
     */
    private function getTableConfig($prefix)
    {
        return [
            'merk' => [
                'primary_key' => 'vehicle_brand_id',
                'foreign_keys' => [],
                'labels' => [
                    'vehicle_brand_name' => 'Nama Merk',
                ],
                'hidden' => ['created_at', 'updated_at'],
            ],

            'varian' => [
                'primary_key' => 'vehicle_varian_id',
                'foreign_keys' => ['vehicle_varian_type_id'],
                'labels' => [
                    'vehicle_varian_name' => 'Nama Varian',
                ],
                'hidden' => ['created_at', 'updated_at'],
            ],

            'statuspenerbitan' => [
                'primary_key' => 'issuance_id',
                'foreign_keys' => [],
                'labels' => [
                    'issuance_name' => 'Nama Status',
                ],
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
