<?php

namespace Jisan\LaravelReadyModular\Generator\Generators\Services;

use Jisan\LaravelReadyModular\Generator\Support\Filesystem;
use Jisan\LaravelReadyModular\Generator\Context\ModuleContext;

class PlainServiceGenerator
{
    public function __construct(private Filesystem $fs) {}

    public function generate(ModuleContext $context): void
    {
        // Ensure Services directory exists
        $dir = "{$context->path}/Services";
        if (! $this->fs->exists($dir)) {
            $this->fs->makeDir($dir);
        }

        $content = <<<PHP
<?php

namespace App\Modules\\{$context->name}\Services;

use App\Modules\\{$context->name}\Models\\{$context->name};

class {$context->name}Service
{
    public function paginate(int \$perPage = 10)
    {
        return {$context->name}::paginate(\$perPage);
    }

    public function find(int \$id)
    {
        return {$context->name}::findOrFail(\$id);
    }

    public function create(array \$data)
    {
        return {$context->name}::create(\$data);
    }

    public function update(int \$id, array \$data)
    {
        \$record = \$this->find(\$id);
        \$record->update(\$data);
        return \$record;
    }

    public function delete(int \$id): bool
    {
        return {$context->name}::findOrFail(\$id)->delete();
    }
}
PHP;

        $this->fs->put(
            "{$context->path}/Services/{$context->name}Service.php",
            $content
        );
    }
}
