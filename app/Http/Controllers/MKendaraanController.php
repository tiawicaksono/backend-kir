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

        $config = $this->getListConfig();

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

    private function getListConfig()
    {
        return [
            'primary_key' => 'id',
            'only_fields' => ['no_uji', 'no_kendaraan', 'nama_pemilik', 'no_rangka', 'no_mesin'],
            'labels' => [
                'no_uji' => 'No Uji',
                'no_kendaraan' => 'No Kendaraan',
                'nama_pemilik' => 'Nama Pemilik',
                'no_rangka' => 'No Rangka',
                'no_mesin' => 'No Mesin',
            ],

            'searchable' => ['no_uji', 'no_kendaraan', 'nama_pemilik', 'no_rangka', 'no_mesin'],
            'sortable' => ['no_uji', 'no_kendaraan', 'nama_pemilik', 'no_rangka', 'no_mesin'],
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
        // validasi minimal aja (biar fleksibel)
        $validator = Validator::make($request->all(), [
            'no_uji' => 'required',
            'nama_pemilik' => 'required',
            'no_rangka' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', $validator->errors(), 422);
        }

        DB::beginTransaction();

        try {
            // ======================
            // 🔹 1. KENDARAAN
            // ======================
            $kendaraanModel = new MKendaraan();
            $kendaraanFields = $kendaraanModel->getFillable();

            $kendaraanData = $request->only($kendaraanFields);

            $kendaraan = MKendaraan::create($kendaraanData);

            // ======================
            // 🔹 2. SPESIFIKASI
            // ======================
            $spesifikasiModel = new MKendaraanSpesifikasi();
            $spesifikasiFields = $spesifikasiModel->getFillable();

            $spesifikasiData = $request->only($spesifikasiFields);

            // pastikan tidak override FK
            unset($spesifikasiData['kendaraan_id']);

            // hanya insert kalau ada isi
            if (collect($spesifikasiData)->filter(function ($v) {
                return $v !== null && $v !== '';
            })->isNotEmpty()) {

                $kendaraan->spesifikasiKendaraan()->create($spesifikasiData);
            }

            DB::commit();

            // load relasi biar langsung siap pakai
            $kendaraan->load($this->getRelations());

            // 🔥 flatten biar konsisten dengan index
            $data = FlattenHelper::flatten([$kendaraan], $this->getDetailConfig());

            return $this->success(
                $data[0],
                'Data kendaraan berhasil dibuat',
                201
            );
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
            'no_uji' => ['required',  Rule::unique('m_kendaraans', 'no_uji')->ignore($id)],
            'no_mesin' => [Rule::unique('m_kendaraans', 'no_mesin')->ignore($id)],
            'no_rangka' => ['required',  Rule::unique('m_kendaraans', 'no_rangka')->ignore($id)],
            'no_srb' => [Rule::unique('m_kendaraans', 'no_srb')->ignore($id)],
            'no_srut' => [Rule::unique('m_kendaraans', 'no_srut')->ignore($id)],
            'no_sut' => [Rule::unique('m_kendaraans', 'no_sut')->ignore($id)],
            'nama_pemilik' => 'required',
            'no_rangka' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', $validator->errors(), 422);
        }

        DB::beginTransaction();

        try {
            // ======================
            // 🔹 1. AMBIL DATA
            // ======================
            $kendaraan = MKendaraan::with('spesifikasiKendaraan')->findOrFail($id);

            // ======================
            // 🔹 2. UPDATE KENDARAAN
            // ======================
            $kendaraanFields = (new MKendaraan())->getFillable();
            $kendaraanData = $request->only($kendaraanFields);

            if (isset($kendaraanData['nama_pemilik'])) {
                $kendaraanData['nama_pemilik'] = strtoupper($kendaraanData['nama_pemilik']);
            }

            $kendaraan->update($kendaraanData);

            // ======================
            // 🔹 3. HANDLE SPESIFIKASI
            // ======================
            $spesifikasiFields = (new MKendaraanSpesifikasi())->getFillable();
            $spesifikasiData = $request->only($spesifikasiFields);

            unset($spesifikasiData['kendaraan_id']);

            // cek apakah ada data yang dikirim
            $hasSpesifikasiInput = collect($spesifikasiData)->filter(function ($v) {
                return $v !== null && $v !== '';
            })->isNotEmpty();

            if ($hasSpesifikasiInput) {
                if ($kendaraan->spesifikasiKendaraan) {
                    // ✅ update existing
                    $kendaraan->spesifikasiKendaraan->update($spesifikasiData);
                } else {
                    // ✅ create baru
                    $kendaraan->spesifikasiKendaraan()->create($spesifikasiData);
                }
            }

            DB::commit();

            // ======================
            // 🔹 4. LOAD RELASI
            // ======================
            $kendaraan->load($this->getRelations());

            // 🔥 flatten biar konsisten dengan index
            $data = FlattenHelper::flatten([$kendaraan], $this->getDetailConfig());

            return $this->success(
                $data[0],
                'Data kendaraan berhasil diupdate',
                200
            );
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
}
