<?php

namespace Jisan\LaravelReadyModular\Generator\Generators\Crud;

use Illuminate\Support\Str;
use Jisan\LaravelReadyModular\Generator\Context\ModuleContext;
use Jisan\LaravelReadyModular\Generator\Contracts\ConditionalGenerator;
use Jisan\LaravelReadyModular\Generator\Support\Filesystem;
use Jisan\LaravelReadyModular\Generator\Support\Stub;
use Jisan\LaravelReadyModular\Generator\Support\StubCompiler;

class RouteGenerator implements ConditionalGenerator
{
    public function __construct(private Filesystem $fs) {}

    public function key(): string
    {
        return 'route';
    }

    public function shouldRun(ModuleContext $context): bool
    {
        return !$context->isShortEntity;
    }

    public function generate(ModuleContext $context): void
    {
        $moduleType = $context->moduleType;
        if ($context->isLinkModule) {
            $moduleType = 'link';
        }

        $stub = Stub::get("{$moduleType}/route.api");

        $replacements = [
            'MODEL' => $context->name,
            'LOWER_MODEL' => Str::snake($context->name), //eg: invoice_item
            'TABLE' => $context->table,
            'ROUTE_PARAM' => strtolower($context->name),
        ];

        if ($context->isLinkModule) {
            $replacements['PLURAL_PARENT_MODEL'] = Str::plural($context->parentName);
            $replacements['plural_parent_model'] = Str::snake($context->parentName);
        }

        $content = StubCompiler::compile($stub, $replacements);

        $this->ensureRouteDirectory($context->path);

        $apiPath = "{$context->path}/Routes/api.php";

        if ($context->isSubEntity() && $this->fs->exists($apiPath)) {
            $existing = $this->fs->get($apiPath);

            // Extract only the route block (skip <?php, use Route)
            $routeBlock = $this->extractRoutesFromStub($content, $context);

            // Append after last Api controller use
            $existing = $this->appendUseStatement($existing, $context);
            $content = rtrim($existing, "\n") . "\n" . $routeBlock;
        }

        $this->fs->put($apiPath, $content);
    }

    private function ensureRouteDirectory(string $modulePath): void
    {
        $dir = "{$modulePath}/Routes";

        if (!$this->fs->exists($dir)) {
            $this->fs->makeDir($dir);
        }
    }

    private function extractRoutesFromStub(string $stubContent, ModuleContext $context): string
    {
        $controller = "{$context->name}Controller";
        $entityPlural = str()->kebab(str()->pluralStudly($context->name));

        $contextNameSnake = Str::snake($context->name); // e.g. invoice_item
        return "\nRoute::crud('{$entityPlural}', {$controller}::class, '{$contextNameSnake}');";
    }

    private function appendUseStatement(string $content, ModuleContext $context): string
    {
        $controller = "{$context->name}Controller";
        $controllerNamespace = "App\\Modules\\{$context->parentName}\\Controllers\\Api\\{$controller}";
        $useLine = "use {$controllerNamespace};";

        if (str_contains($content, $useLine)) {
            return $content;
        }

        preg_match_all('/^use App\\\\Modules\\\\.*?Controllers\\\\Api\\\\.*?;/m', $content, $matches);

        if (!empty($matches[0])) {
            $lastUse = end($matches[0]);
            return str_replace($lastUse, $lastUse . "\n" . $useLine, $content);
        }

        return preg_replace('/<\?php\s*/', "<?php\n\n{$useLine}\n\n", $content, 1);
    }
}
