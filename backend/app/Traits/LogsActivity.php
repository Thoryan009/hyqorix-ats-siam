<?php

namespace App\Traits;

use App\Modules\Shared\Loggers\ActivityLogger;


trait LogsActivity
{
    protected array $activityOldValues = [];

    public static function bootLogsActivity()
    {
        static::created(fn($model) => self::logActivity($model, 'created'));
        static::updated(fn($model) => self::logActivity($model, 'updated'));
        static::deleted(fn($model) => self::logActivity($model, 'deleted'));
    }

    protected static function logActivity($model, $action)
    {
        $description = self::buildDescription(
            $model,
            $action
        );

        $context = [];

        if ($action === 'updated') {
            $context = self::resolveChanges($model);
        }

        ActivityLogger::log(
            $action,
            $description,
            $context
        );
    }
    protected static function resolveIdentifier($model)
    {
        if (method_exists($model, 'getActivityIdentifier')) {
            return $model->getActivityIdentifier();
        }

        return $model->name
            ?? $model->title
            ?? $model->email
            ?? "#{$model->id}";
    }



    protected static function resolveChanges($model): array
    {
        $changes = [];

        foreach ($model->getChanges() as $field => $newValue) {

            if (in_array($field, ['created_at', 'updated_at', 'created_by', 'updated_by'])) {
                continue;
            }

            $oldValue = $model->getOriginal($field);

            $changes[$field] = [
                'old' => self::humanReadableValue(
                    $model,
                    $field,
                    $oldValue
                ),
                'new' => self::humanReadableValue(
                    $model,
                    $field,
                    $newValue
                ),
            ];
        }


    // Always include updated_by if column exists
    if (
        isset($model->updated_by)
        && !empty($model->updated_by)
    ) {
        $changes['updated_by'] = [
            'value' => self::humanReadableValue(
                $model,
                'updated_by',
                $model->updated_by
            )
        ];
    }

        \Log::info('Resolved activity changes: ', $changes); // Debug log
        return $changes;
    }

    protected static function humanReadableValue(
    $model,
    string $field,
    $value
    )
    {
        $relationMap = [
            'country_id' => \App\Modules\Country\Models\Country::class,
            'user_id' => \App\Modules\Auth\Models\User::class,
            'client_id' => \App\Modules\Client\Models\Client::class,
            'job_list_id' => \App\Modules\JobList\Models\JobList::class,
            'agent_id' => \App\Modules\Agent\Models\Agent::class,
            'principal_id' => \App\Modules\Principal\Models\Principal::class,
            'qualification_id' => \App\Modules\Application\Models\Qualification::class,
            'subject_id' => \App\Modules\Application\Models\Subject::class,
            'work_order_id' => \App\Modules\WorkOrder\Models\WorkOrder::class,
            'employee_id' => \App\Modules\Employee\Models\Employee::class,
            'department_id' => \App\Modules\Employee\Models\Department::class,
            'created_by' => \App\Modules\Auth\Models\User::class,
            'updated_by' => \App\Modules\Auth\Models\User::class,
            'job_list_details_head_id' => \App\Modules\JobList\Models\JobListDetailsHead::class,
            'job_list_details_category_id' => \App\Modules\JobList\Models\JobListDetailsCategory::class,

        ];

        if (! isset($relationMap[$field])) {
            return $value;
        }

        $related = $relationMap[$field]::find($value);


        if (! $related) {
            return $value;
        }

        if ($related instanceof \App\Modules\Agent\Models\Agent) {
        return $related->user?->name ?? "#{$related->id}";
        }

        if ($related instanceof \App\Modules\Client\Models\Client) {
            return $related->user?->name ?? "#{$related->id}";
        }

        if ($related instanceof \App\Modules\Employee\Models\Employee) {
            return $related->user?->name ?? "#{$related->id}";
        }

        if ($related instanceof \App\Modules\Principal\Models\Principal) {
            return $related->user?->name ?? "#{$related->id}";
        }

        return $related->name
            ?? $related->title
            ?? $related->email
            ?? $related->work_order_id
            ?? "#{$related->id}";
    }

 protected static function buildDescription(
    $model,
    string $action
): string {

    $user = auth()->user()?->name ?? 'System';

    $modelName = class_basename($model);

    $identifier = self::resolveIdentifier($model);

    if ($action !== 'updated') {
        return "{$user} {$action} {$modelName} '{$identifier}'";
    }

    $changes = self::resolveChanges($model);

    if (empty($changes)) {
        return "{$user} updated {$modelName} '{$identifier}'";
    }

    $firstChange = reset($changes);

    $old = $firstChange['old'] ?? '';
    $new = $firstChange['new'] ?? '';

    //check for application process
    if (is_array($old)) {
        $old = json_encode($old);
    }

    if (is_array($new)) {
        $new = json_encode($new);
    }

    return "{$user} updated {$modelName} '{$old}' to '{$new}'";
}
}
