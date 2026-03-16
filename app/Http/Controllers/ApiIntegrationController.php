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
            return response()->json([
                'message' => 'Invalid prefix'
            ], 404);
        }

        $model = $modelMap[$prefix];

        $query = $model::query();

        // SEARCH
        if ($request->search) {

            $columns = array_diff(
                Schema::getColumnListing((new $model)->getTable()),
                ['created_at', 'updated_at']
            );

            $query->where(function ($q) use ($columns, $request) {
                $keyword = strtolower($request->search);
                foreach ($columns as $col) {
                    $q->orWhereRaw("LOWER(CAST($col AS TEXT)) LIKE ?", ["%{$keyword}%"]);
                }
            });
        }

        // SORT
        if ($request->sort) {
            $query->orderBy($request->sort, $request->order ?? 'asc');
        }

        $perPage = $request->per_page ?? 10;

        return $query->paginate($perPage);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ApiIntegration $apiIntegration)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ApiIntegration $apiIntegration)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ApiIntegration $apiIntegration)
    {
        //
    }
}
