<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MasterKelurahanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kecamatan_id' => $this->kecamatan_id,
            'nama_kelurahan' => $this->nama_kelurahan,

            // ambil dari relasi
            'nama_kecamatan' => $this->whenLoaded('kecamatan', function () {
                return $this->kecamatan->nama_kecamatan;
            }),
        ];
    }
}
