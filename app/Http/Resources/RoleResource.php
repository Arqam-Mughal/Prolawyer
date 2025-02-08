<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
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
            'name' => $this->name,
            'price' => $this->price,
            'quarterly_price' => $this->quarterly_price,
            'yearly_price' => $this->yearly_price,
            'type' => $this->type,
            'no_cases' => $this->no_cases,
            'status' => $this->status,
        ];
    }
}
