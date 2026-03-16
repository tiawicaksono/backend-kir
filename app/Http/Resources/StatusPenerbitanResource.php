<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StatusPenerbitanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'issuanceId' => $this->issuance_id,
            'issuanceCode' => $this->issuance_code,
            'issuanceName' => $this->issuance_name,
            'issuanceDesc' => $this->issuance_desc,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
