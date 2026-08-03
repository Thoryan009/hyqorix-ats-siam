<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait HandlesClientFilter
{
    protected function applyClientFilter(Request $request, array $filters): array
    {
        $filters['client_id'] = $request->get('client_id');

        $user = auth()->user();

        if ($user && $user->type === 'client') {
            $filters['client_id'] = $user?->client?->id;
        }

        return $filters;
    }
}
