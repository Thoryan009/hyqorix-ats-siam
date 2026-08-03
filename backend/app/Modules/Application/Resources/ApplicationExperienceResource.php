<?php
namespace App\Modules\Application\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Application\Models\ApplicationExperience;
class ApplicationExperienceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'company_name' => $this->company_name,
            'position' => $this->position,
            'from_date' => $this->from_date,
            'to_date' => $this->to_date,
            'description' => $this->description,
        ];
    }
}
