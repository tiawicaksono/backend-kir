<?php

namespace App\Http\Controllers;

use App\Models\ApiToken;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Client\RequestException;

class ApiCekDataController extends BaseApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nouji' => 'required|string|max:255',
            'prefix' => 'required|string|max:255'
        ]);


        if ($validator->fails()) {
            return $this->error(
                'Validation error',
                $validator->errors(),
                422
            );
        }

        $type = $validator->validated()['prefix'];
        $no_uji = $validator->validated()['nouji'];

        if ($type === 'lastexam') {
            $prefix = $type;
            $param = [
                'nouji' => $no_uji
            ];
        } else {
            $prefix = 'checkpengujiankeluar';
            $param = [
                'nouji' => $no_uji,
                'statuspenerbitan' => $type
            ];
        }

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
            /** @var Response $response */
            $response = Http::retry(3, 1000)
                ->timeout(15)
                ->withoutVerifying()
                ->withHeaders([
                    'Content-Type' => 'application/json'
                ])
                ->post($url_api, [
                    "token" => $token,
                    "prefix" => $prefix,
                    "param" => $param
                ]);

            if (!$response->successful()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal hit API eksternal',
                    'data' => $response->json()
                ], $response->status());
            }

            return response()->json([
                'status' => true,
                'message' => 'success',
                'data' => $response->json()['data'] ?? $response->json()
            ]);
        } catch (RequestException $e) {
            // ✅ khusus HTTP error (punya response)
            $body = json_decode($e->response->body(), true);

            return response()->json([
                'status' => false,
                'message' => $body['message'] ?? 'API error',
                'code' => $body['code'] ?? null,
                'data' => $body
            ], $e->response->status());
        } catch (\Exception $e) {
            // ✅ fallback error umum (tidak ada response)
            return response()->json([
                'status' => false,
                'message' => 'Server error: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
