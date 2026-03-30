<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MasterKotaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'provinsi_id' => $this->provinsi_id,
            'nama_kota' => $this->nama_kota,

            // ambil dari relasi
            'nama_provinsi' => $this->whenLoaded('provinsi', function () {
                return $this->provinsi->nama_provinsi;
            }),
        ];
    }
}
