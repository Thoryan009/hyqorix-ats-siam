<?php

namespace App\Modules\Parties\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartySourceOptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $item = is_array($this->resource) ? $this->resource : [
            'id' => $this->id ?? null,
            'code' => $this->code ?? null,
            'name' => $this->name ?? null,
        ];

        return [
            'id' => $item['id'] ?? null,
            'code' => $item['code'] ?? null,
            'name' => $item['name'] ?? null,
        ];
    }
}
