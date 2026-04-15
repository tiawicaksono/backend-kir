<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrnSinkronResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            // 'apiIntegrationId' => $this->api_integration_id,
            'api_integration_id' => $this->api_integration_id,
            'name' => $this->name,
            'urlApi' => $this->url_api,
            'token' => $this->token,
            'prefix' => $this->prefix,
            'status' => (bool) $this->status,
            'keterangan' => $this->keterangan,
            // 'createdAt' => $this->created_at
            'created_at' => $this->created_at
        ];
    }
}
