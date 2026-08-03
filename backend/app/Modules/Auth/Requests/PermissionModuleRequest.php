<?php

namespace App\Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PermissionModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $module = strtolower(trim((string) $this->input('module', '')));
        $module = preg_replace('/[^a-z0-9_]+/', '_', $module) ?: '';
        $module = trim($module, '_');

        $actions = $this->input('actions', ['create', 'edit', 'view', 'delete']);
        if (!is_array($actions)) {
            $actions = [];
        }

        $normalizedActions = [];
        foreach ($actions as $action) {
            $action = strtolower(trim((string) $action));
            $action = preg_replace('/[^a-z0-9_]+/', '_', $action) ?: '';
            $action = trim($action, '_');
            if ($action !== '') {
                $normalizedActions[] = $action;
            }
        }

        $this->merge([
            'module' => $module,
            'actions' => array_values(array_unique($normalizedActions)),
            'label' => trim((string) ($this->input('label') ?: str_replace('_', ' ', $module))),
            'assign_to_admin' => filter_var($this->input('assign_to_admin', true), FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    public function rules(): array
    {
        return [
            'module' => ['required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_]*$/'],
            'actions' => ['required', 'array', 'min:1'],
            'actions.*' => ['required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_]*$/'],
            'label' => ['nullable', 'string', 'max:255'],
            'assign_to_admin' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'module.regex' => 'Module must start with a letter and use only lowercase letters, numbers, and underscores.',
            'actions.*.regex' => 'Each action must start with a letter and use only lowercase letters, numbers, and underscores.',
        ];
    }
}
