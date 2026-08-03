<?php

namespace App\Modules\Shared\Services;

use Illuminate\Http\Request;

class FilterResolver
{
    public function resolve(Request $request, array $availableFilters): array
    {
        $filters = $request->filters() ?? [];

        $allowedKeys = collect($availableFilters)
            ->pluck('key')
            ->toArray();

        foreach ($allowedKeys as $key) {
            if ($request->filled($key)) {
                $filters[$key] = $request->input($key);
            }
        }

        return $filters;
    }
}
