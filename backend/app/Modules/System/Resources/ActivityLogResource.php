<?php

namespace App\Modules\System\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Shared\Helpers\DateTimeFormatter;

class ActivityLogResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user_name' => $this->user ? $this->user->name : null,
            'action' => $this->action,
            'description' => $this->description,

            'user' => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ] : null,
              'context' => $this->formattedChanges(),
            'created_at' => DateTimeFormatter::formatDateTime($this->created_at),
        ];
    }

//     protected function formattedChanges(): ?string
//     {
//         if (empty($this->context)) {
//             return null;
//         }

//         $changes = [];

//         foreach ($this->context as $field => $values) {

//             $fieldName = str($field)
//                 ->replace('_', ' ')
//                 ->title();

//             // Special case
//             if (isset($values['value'])) {

//                 $changes[] =
//                     "{$fieldName}: {$values['value']}";

//                 continue;
//             }

//             foreach ($this->context as $field => $values) {

//     \Log::info([
//         'field' => $field,
//         'old_type' => gettype($values['old'] ?? null),
//         'new_type' => gettype($values['new'] ?? null),
//         'value_type' => gettype($values['value'] ?? null),
//     ]);
// }
// // \Log::info([
// //     'field' => $field,
// //     'values' => $values,
// // ]);
//             $changes[] =
//                 "{$fieldName}: {$values['old']} → {$values['new']}";
//         }

//         return implode(', ', $changes);
//     }
protected function formattedChanges(): ?string
{
    if (empty($this->context)) {
        return null;
    }

    $changes = [];

    foreach ($this->context as $field => $values) {

        $fieldName = str($field)
            ->replace('_', ' ')
            ->title();

        // updated_by type
        if (isset($values['value'])) {

            $changes[] = "{$fieldName}: {$values['value']}";

            continue;
        }

        $old = $values['old'] ?? null;
        $new = $values['new'] ?? null;

        if (is_array($old)) {
            $old = json_encode(
                $old,
                JSON_UNESCAPED_UNICODE
            );
        }

        if (is_array($new)) {
            $new = json_encode(
                $new,
                JSON_UNESCAPED_UNICODE
            );
        }

        $changes[] =
            "{$fieldName}: {$old} → {$new}";
    }

    return implode(', ', $changes);
}
}
