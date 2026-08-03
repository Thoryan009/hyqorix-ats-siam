<?php

namespace App\Modules\Country\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Shared\Helpers\DateTimeFormatter;

class CountryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'country_image_url' => $this->country_image_url,
            'clients' => $this->clients->map(function ($client) {
                return [
                    'id' => $client->id,
                    'client_id' => $client->client_id,
                    'name' => $client->user->name,
                    'email' => $client->user->email,
                    'phone' => $client->user->phone,
                    'type' => $client->user->type,
                    'country_id' => $client->country_id,
                    'country' => $client->country->name,
                    'created_at' => DateTimeFormatter::formatDateTime($client->created_at),
                    'updated_at' => DateTimeFormatter::formatDateTime($client->updated_at),
                    'created_by' => $client->createdBy ? $client->createdBy->name : null,
                    'updated_by' => $client->updatedBy ? $client->updatedBy->name : null,
                ];
            }),


            'created_at' => DateTimeFormatter::formatDateTime($this->created_at),
            'updated_at' => DateTimeFormatter::formatDateTime($this->updated_at),
            'created_by' => $this->createdBy ? $this->createdBy->name : null,
            'updated_by' => $this->updatedBy ? $this->updatedBy->name : null,
        ];
    }
}
