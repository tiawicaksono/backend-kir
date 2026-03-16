<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KelasJalanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'kelasjalanId' => $this->kelasjalan_id,
            'kelasjalanCode' => $this->kelasjalan_code,
            'kelasjalanName' => $this->kelasjalan_name,
            'kelasjalanDesc' => $this->kelasjalan_desc,
            'muatanSumbuTerberat' => $this->muatan_sumbu_terberat,
            'vehicleLength' => $this->vehicle_length,
            'vehicleHeight' => $this->vehicle_height,
            'vehicleWidth' => $this->vehicle_width,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
