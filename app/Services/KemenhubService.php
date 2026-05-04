<?php

namespace App\Services;

use App\Models\ApiToken;
use App\Models\TrnSinkron;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class KemenhubService
{
    public function getDataSync(string $urlApi, string $token, string $prefix)
    {
        /**
         * retry 3x
         * delay 1 detik
         * timeout 10 detik
         */
        /** @var Response $response */
        $response = Http::retry(3, 1000)->timeout(10)->withoutVerifying()->withHeaders([
            'Content-Type' => 'application/json'
        ])->post($urlApi, [
            "token" => $token,
            "prefix" => $prefix
        ]);

        if (!$response->successful()) {
            throw new \Exception("Gagal request API eksternal");
        }

        return $response->json();
    }

    public function sync($payload, callable $handler)
    {
        $apiToken = ApiToken::where('is_active', true)->first();

        if (!$apiToken) {
            return [
                'success' => false,
                'message' => 'Api Token tidak ditemukan atau tidak aktif',
                'transaction' => null
            ];
        }

        $url_api = $apiToken->url_api;
        $token   = $apiToken->token;

        try {
            $result = $this->getDataSync(
                $url_api,
                $token,
                $payload['prefix']
            );

            DB::beginTransaction();

            foreach ($result['data'] ?? [] as $item) {
                $handler($item); // 🔥 selalu 1 item
            }

            $transaction = TrnSinkron::create([
                'api_integration_id' => $payload['api_integration_id'],
                'name' => $payload['name'],
                'prefix' => $payload['prefix'],
                'url_api' => $url_api,
                'token' => $token,
                'status' => true,
                'keterangan' => 'Sinkronisasi berhasil',
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Sinkronisasi berhasil',
                'transaction' => $transaction
            ];
        } catch (\Exception $e) {

            DB::rollBack();

            $transaction = TrnSinkron::create([
                'api_integration_id' => $payload['api_integration_id'],
                'name' => $payload['name'],
                'prefix' => $payload['prefix'],
                'url_api' => $url_api,
                'token' => $token,
                'status' => false,
                'keterangan' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'transaction' => $transaction
            ];
        }
    }

    /**
     * ===============================
     * CHECK NUMPANG DAN MUTASI
     * ===============================
     */
    public function checkPengujianKeluar(string $noUji, int $statusPenerbitan)
    {
        $apiToken = ApiToken::where('is_active', true)->first();

        if (!$apiToken) {
            throw new \Exception("Api Token tidak aktif");
        }

        $response = Http::retry(3, 1000)
            ->timeout(10)
            ->withoutVerifying()
            ->withHeaders([
                'Content-Type' => 'application/json'
            ])
            ->post($apiToken->url_api, [
                "token" => $apiToken->token,
                "prefix" => "checkpengujiankeluar",
                "param" => [
                    "nouji" => $noUji,
                    "statuspenerbitan" => $statusPenerbitan
                ]
            ]);

        if (!$response->successful()) {
            throw new \Exception("Gagal request ke Kementrian");
        }

        $json = $response->json();

        if (!($json['status'] ?? false)) {
            return null;
        }

        return $json['data'] ?? null;
    }

    public function mapToKendaraan(array $data): array
    {
        return [
            'no_uji' => $data['exam_code'] ?? null,
            'no_mesin' => $data['nomesin'] ?? null,
            'no_rangka' => $data['norangka'] ?? null,
            'no_kendaraan' => $data['nonrkb'] ?? null,
            'nama_pemilik' => $data['owner_name'] ?? null,
            'alamat' => $data['owner_address'] ?? null,
            'no_identitas' => $data['owner_nik'] ?? null,
        ];
    }
}
