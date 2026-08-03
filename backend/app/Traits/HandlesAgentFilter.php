<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait HandlesAgentFilter
{
    protected function applyAgentFilter(Request $request, array $filters): array
    {
        $filters['agent_id'] = $request->get('agent_id');

        $user = auth()->user();

        if ($user && $user->type === 'agent') {
            $filters['agent_id'] = $user?->agent?->id;
        }

        return $filters;
    }
}