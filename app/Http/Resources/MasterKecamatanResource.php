<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MasterKecamatanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kota_id' => $this->kota_id,
            'nama_kecamatan' => $this->nama_kecamatan,

            // ambil dari relasi
            'nama_kota' => $this->whenLoaded('kota', function () {
                return $this->kota->nama_kota;
            }),
        ];
    }
}
