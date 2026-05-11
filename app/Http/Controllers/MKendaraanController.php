<?php

namespace App\Http\Controllers;

use App\Helpers\FlattenHelper;
use App\Models\MKendaraan;
use App\Models\MKendaraanSpesifikasi;
use App\Services\QueryFilterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MKendaraanController extends BaseApiController
{
    public function counts()
    {
        return response()->json([
            'countData' => MKendaraan::count(),
        ]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $model = MKendaraan::class;
        // $query = $model::with($this->getRelations());
        $query = $model::query();

        $config = $this->getTableConfig();

        // 🔥 DEFAULT SORT PER MODULE
        $primaryKey = $config['primary_key'] ?? null;

        if (!$request->filled('sort_by') && $primaryKey) {
            $request->merge([
                'sort_by' => $primaryKey,
                'sort_order' => 'desc',
            ]);
        }

        QueryFilterService::apply($query, $request, $model, $config);
        // dd($query->toSql(), $query->getBindings());

        $perPage = $request->limit ?? 10;
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

    private function getTableConfig()
    {
        return [
            'primary_key' => 'id',
            'only_fields' => ['id', 'no_uji', 'no_kendaraan', 'nama_pemilik', 'no_rangka', 'no_mesin'],
            'labels' => [
                'no_uji' => 'No Uji',
                'no_kendaraan' => 'No Kendaraan',
                'nama_pemilik' => 'Nama Pemilik',
                'no_rangka' => 'No Rangka',
                'no_mesin' => 'No Mesin',
            ],
            'searchable' => [
                [
                    'field' => 'no_uji',
                    'label' => 'No Uji'
                ],
                [
                    'field' => 'no_kendaraan',
                    'label' => 'No Kendaraan'
                ],
                [
                    'field' => 'nama_pemilik',
                    'label' => 'Nama Pemilik'
                ],
                [
                    'field' => 'no_rangka',
                    'label' => 'No Rangka'
                ],
                [
                    'field' => 'no_mesin',
                    'label' => 'No Mesin'
                ],
            ],
            // 'sortable' => ['no_uji', 'no_kendaraan', 'nama_pemilik', 'no_rangka', 'no_mesin'],
        ];
    }

    public function getDetailConfig()
    {
        return [
            'primary_key' => 'id',
            'only' => [
                'merk' => [
                    'only' => ['vehicle_brand_name']
                ],

                'jenisKendaraan' => [
                    'only' => ['vehicle_type_name']
                ],

                'subJenisKendaraan' => [
                    'only' => ['vehicle_sub_name']
                ],

                'varianMerk' => [
                    'only' => ['vehicle_varian_type_name']
                ],

                'tipeVarianMerk' => [
                    'only' => ['vehicle_varian_name']
                ],

                'bahanUtama' => [
                    'only' => ['bahan_utama']
                ],

                'provinsi' => [
                    'only' => ['nama_provinsi'],
                    'alias' => ['nama_provinsi' => 'provinsi']
                ],

                'kota' => [
                    'only' => ['nama_kota']
                ],

                'kecamatan' => [
                    'only' => ['nama_kecamatan']
                ],

                'kelurahan' => [
                    'only' => ['nama_kelurahan']
                ],
                'spesifikasiKendaraan' => [
                    'only' => '*',
                    'except' => ['kendaraan_id', 'deleted_at', 'created_at', 'updated_at'],
                    'children' => [
                        'bahanBakar' => [
                            'only' => ['fuel_name'],
                            'alias' => ['fuel_name' => 'bahan_bakar']
                        ],
                        'konfigurasiSumbu' => [
                            'only' => ['name'],
                            'alias' => ['name' => 'konfigurasi_sumbu']
                        ],
                        'kelasJalan' => [
                            'only' => ['kelasjalan_name'],
                            'alias' => ['kelasjalan_name' => 'kelas_jalan']
                        ],
                    ]
                ],
            ],

            'labels' => [
                'no_uji' => 'No Uji',
                'no_kendaraan' => 'No Kendaraan',
                'no_rangka' => 'No Rangka',
                'no_mesin' => 'No Mesin',
                'nama_pemilik' => 'Nama Pemilik',
                'mst' => 'MST',
            ],
            'searchable' => ['no_uji', 'no_kendaraan', 'nama_pemilik', 'no_rangka', 'no_mesin'],
            'sortable' => ['no_uji', 'no_kendaraan', 'nama_pemilik'],
            'hidden' => ['deleted_at', 'created_at', 'updated_at'],
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'no_uji' => 'sometimes|required',
            'nama_pemilik' => 'sometimes|required',
            'no_rangka' => 'sometimes|required',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', $validator->errors(), 422);
        }

        DB::beginTransaction();

        try {
            $kendaraanFields = (new MKendaraan())->getFillable();

            $kendaraanData = collect($request->only($kendaraanFields))
                ->filter(fn($v) => $v !== null && $v !== '');

            // uppercase kalau ada
            if ($kendaraanData->has('nama_pemilik')) {
                $kendaraanData['nama_pemilik'] = strtoupper($kendaraanData['nama_pemilik']);
            }

            $kendaraan = MKendaraan::create($kendaraanData->toArray());

            // ======================
            // SPESIFIKASI
            // ======================
            $spesifikasiFields = (new MKendaraanSpesifikasi())->getFillable();

            $spesifikasiData = collect($request->only($spesifikasiFields))
                ->filter(fn($v) => $v !== null && $v !== '');

            if ($spesifikasiData->isNotEmpty()) {
                $kendaraan->spesifikasiKendaraan()->create($spesifikasiData->toArray());
            }

            DB::commit();

            $kendaraan->load($this->getRelations());

            $data = FlattenHelper::flatten([$kendaraan], $this->getDetailConfig());

            return $this->success($data[0], 'Data kendaraan berhasil dibuat', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);

            return $this->error('Data gagal disimpan', null, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id = null)
    {
        try {
            $query = MKendaraan::with($this->getRelations());

            // =========================
            // 1. FILTER VIA QUERYFILTERSERVICE (REUSE LOGIC)
            // =========================
            QueryFilterService::apply(
                $query,
                $request,
                MKendaraan::class,
                $this->getDetailConfig()
            );

            // =========================
            // 2. ID OVERRIDE (paling prioritas)
            // =========================
            if ($id) {
                $query->where('id', $id);
            }

            // =========================
            // 3. RESULT
            // =========================
            $kendaraan = $query->first();

            if (!$kendaraan) {
                return $this->error('Data tidak ditemukan', null, 404);
            }
            // dd([
            //     'loaded' => $kendaraan->relationLoaded('spesifikasiKendaraan'),
            //     'data' => $kendaraan->spesifikasiKendaraan,
            // ]);
            // =========================
            // 4. FLATTEN
            // =========================
            $data = FlattenHelper::flatten(
                [$kendaraan],
                $this->getDetailConfig()
            );

            return $this->success(
                $data[0],
                'Detail kendaraan berhasil diambil'
            );
        } catch (\Exception $e) {
            Log::error($e);

            return $this->error('Gagal mengambil data', null, 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    private function normalize($value)
    {
        return $value ? strtoupper(str_replace(' ', '', $value)) : $value;
    }
    private function getNormalizeFields()
    {
        return [
            // 'no_uji',
            'no_mesin',
            'no_rangka',
            'no_srb',
            'no_srut',
            'no_sut',
        ];
    }
    private function normalizeRequest(Request $request)
    {
        $data = $request->all();

        foreach ($this->getNormalizeFields() as $field) {
            if (isset($data[$field])) {
                $data[$field] = $this->normalize($data[$field]);
            }
        }

        $request->merge($data);
    }

    public function update(Request $request, $id)
    {
        $this->normalizeRequest($request);

        $validator = Validator::make($request->all(), [
            'no_uji' => ['sometimes', Rule::unique('m_kendaraans', 'no_uji')->ignore($id)],
            'no_mesin' => ['sometimes', Rule::unique('m_kendaraans', 'no_mesin')->ignore($id)],
            'no_rangka' => ['sometimes', Rule::unique('m_kendaraans', 'no_rangka')->ignore($id)],
            'no_srb' => ['sometimes', 'nullable', Rule::unique('m_kendaraans', 'no_srb')->ignore($id)],
            'no_srut' => ['sometimes', 'nullable', Rule::unique('m_kendaraans', 'no_srut')->ignore($id)],
            'no_sut' => ['sometimes', 'nullable', Rule::unique('m_kendaraans', 'no_sut')->ignore($id)],
            'nama_pemilik' => 'sometimes|required',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', $validator->errors(), 422);
        }

        DB::beginTransaction();

        try {
            $kendaraan = MKendaraan::with('spesifikasiKendaraan')->findOrFail($id);

            // ======================
            // 🔹 UPDATE KENDARAAN (PARTIAL)
            // ======================
            $kendaraanFields = (new MKendaraan())->getFillable();

            $kendaraanData = collect($request->only($kendaraanFields))
                ->filter(fn($v) => $v !== null && $v !== '');

            if ($kendaraanData->has('nama_pemilik')) {
                $kendaraanData['nama_pemilik'] = strtoupper($kendaraanData['nama_pemilik']);
            }

            if ($kendaraanData->isNotEmpty()) {
                $kendaraan->update($kendaraanData->toArray());
            }

            // ======================
            // 🔹 UPDATE SPESIFIKASI
            // ======================
            $spesifikasiFields = (new MKendaraanSpesifikasi())->getFillable();

            $spesifikasiData = collect($request->only($spesifikasiFields))
                ->filter(fn($v) => $v !== null && $v !== '');

            if ($spesifikasiData->isNotEmpty()) {
                if ($kendaraan->spesifikasiKendaraan) {
                    $kendaraan->spesifikasiKendaraan->update($spesifikasiData->toArray());
                } else {
                    $kendaraan->spesifikasiKendaraan()->create($spesifikasiData->toArray());
                }
            }

            DB::commit();

            $kendaraan->load($this->getRelations());

            $data = FlattenHelper::flatten([$kendaraan], $this->getDetailConfig());

            return $this->success($data[0], 'Data kendaraan berhasil diupdate', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);

            return $this->error('Data gagal diupdate', null, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $kendaraan = MKendaraan::findOrFail($id);

            // 🔥 cukup ini (cascade dari model)
            $kendaraan->delete();

            DB::commit();

            return $this->success(
                null,
                'Data kendaraan berhasil dihapus',
                200
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);

            return $this->error('Data gagal dihapus', null, 500);
        }
    }

    /**
     * Force delete the specified resource from storage.
     */
    public function forceDelete($id)
    {
        DB::beginTransaction();

        try {
            $kendaraan = MKendaraan::withTrashed()->findOrFail($id);

            $kendaraan->forceDelete();

            DB::commit();

            return $this->success(null, 'Data berhasil dihapus permanen', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);

            return $this->error('Gagal force delete', null, 500);
        }
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore($id)
    {
        DB::beginTransaction();

        try {
            $kendaraan = MKendaraan::withTrashed()->findOrFail($id);

            // 🔥 cukup ini (cascade dari model)
            $kendaraan->restore();

            DB::commit();

            return $this->success(null, 'Data berhasil direstore', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);

            return $this->error('Gagal restore', null, 500);
        }
    }

    private function getRelations()
    {
        return [
            'provinsi',
            'kota',
            'kecamatan',
            'kelurahan',
            'merk',
            'varianMerk',
            'tipeVarianMerk',
            'jenisKendaraan',
            'subJenisKendaraan',
            'bahanUtama',
            'spesifikasiKendaraan',
            'spesifikasiKendaraan.bahanBakar',
            'spesifikasiKendaraan.konfigurasiSumbu',
            'spesifikasiKendaraan.kelasJalan',
        ];
    }

    public function riwayatUji()
    {
        $jenis = ["Uji Pertama", "Berkala", "Numpang Uji Keluar"];
        $hasil = ["Lulus", "Tidak Lulus"];
        $penguji = ["SUTOPO, A.Ma, PKB., S.T.", "LEDYS KARTONO PUTERA, A.Ma. PKB., SH."];

        $data = collect(range(1, 17))->map(function ($i) use ($jenis, $hasil, $penguji) {
            return [
                "id" => $i,
                "tanggal_uji" => now()->subDays(rand(10, 500))->format('Y-m-d'),
                "jenis_uji" => $jenis[array_rand($jenis)],
                "nama_penguji" => $penguji[array_rand($penguji)],
                "hasil_uji" => $hasil[array_rand($hasil)],
            ];
        });

        return response()->json($data);
    }
}
