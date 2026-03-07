<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class KemenhubService
{
    public function getStatusPenerbitan(string $urlApi, string $token, string $prefix)
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
}
