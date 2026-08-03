<?php

namespace Jisan\LaravelReadyModular\Generator\Context;

use Illuminate\Support\Str;

final class ModuleContext
{
    public string $name;
    public string $table;
    public string $path;

    public ?string $parentName = null;
    public ?string $parentPath = null;

    public string $moduleType;
    public string $serviceType;

    public bool $isSubEntity = false;
    public bool $isShortEntity = false;
    public bool $isLinkModule = false;

    public function __construct(
        string $name,
        string $moduleType = 'crud',
        string $serviceType = 'plain',
        ?string $parentName = null
    ) {
        $this->name        = Str::studly($name);
        $this->table       = Str::plural(Str::snake($this->name));
        $this->moduleType  = $moduleType;
        $this->serviceType = $serviceType;

        $this->path = app_path("Modules/{$this->name}");

        if ($parentName !== null) {
            $this->parentName = Str::studly($parentName);
            $this->parentPath = app_path("Modules/{$this->parentName}");
        }
    }

    /* ---------- State markers ---------- */

    public function markAsSubEntity(): self
    {
        $this->isSubEntity = true;
        return $this;
    }

    public function isSubEntity(): bool
    {
        return $this->isSubEntity;
    }

    public function markAsShortEntity(): self
    {
        $this->isShortEntity = true;
        return $this;
    }

    public function markAsLinkModule(): self
    {
        $this->isLinkModule = true;
        return $this;
    }

    /* ---------- Helpers (IMPORTANT) ---------- */

    public function hasParent(): bool
    {
        return $this->parentName !== null;
    }

    public function isLinked(): bool
    {
        return $this->isLinkModule && $this->hasParent();
    }
}
