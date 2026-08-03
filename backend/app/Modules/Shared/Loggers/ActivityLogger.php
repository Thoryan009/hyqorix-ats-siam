<?php

namespace App\Modules\Shared\Loggers;

use App\Modules\Employee\Models\Employee;
use App\Modules\System\Models\ActivityLog;

class ActivityLogger
{
    public static function log($action, $description = null, array $context = [])
    {
        $user = auth()->user();

        if ($user && $user->type === 'employee') {
            $employee = Employee::where('user_id', $user->id)->first();

            if ($employee) {
                $employee->incrementQuietly('points', 2);
            }
        }

        ActivityLog::create([
            'user_id' => auth()->id() ?? 1,
            'action' => $action,
            'description' => $description,
            'context' => $context,
        ]);
    }
}
