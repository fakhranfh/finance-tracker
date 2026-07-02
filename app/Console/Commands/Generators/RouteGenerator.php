<?php

namespace App\Console\Commands\Generators;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class RouteGenerator
{
    private Filesystem $filesystem;

    public function __construct()
    {
        $this->filesystem = new Filesystem;
    }

    public function generate(string $name, string $label, callable $callback): void
    {
        $routeName = Str::kebab($label);
        $webRoutePath = base_path('routes/web.php');
        $webRouteContent = $this->filesystem->get($webRoutePath);

        $this->addControllerImport($name, $webRoutePath, $webRouteContent, $callback);
        $this->appendRouteGroup($name, $label, $routeName, $webRoutePath, $callback);
    }

    private function addControllerImport(string $name, string $webRoutePath, string &$webRouteContent, callable $callback): void
    {
        if (strpos($webRouteContent, "use App\\Http\\Controllers\\{$name}Controller;") !== false) {
            return;
        }

        $lines = explode("\n", $webRouteContent);
        $lastUseIndex = -1;
        foreach ($lines as $i => $line) {
            if (preg_match('/^use\s+[\w\\\\]+;/', $line)) {
                $lastUseIndex = $i;
            }
        }

        $importLine = "use App\\Http\\Controllers\\{$name}Controller;";
        if ($lastUseIndex !== -1) {
            array_splice($lines, $lastUseIndex + 1, 0, $importLine);
        } else {
            foreach ($lines as $i => $line) {
                if (strpos($line, '<?php') !== false) {
                    array_splice($lines, $i + 1, 0, $importLine);
                    break;
                }
            }
        }

        $webRouteContent = implode("\n", $lines);
        $this->filesystem->put($webRoutePath, $webRouteContent);
        $callback("Import for {$name}Controller added to routes/web.php.", 'info');
    }

    private function appendRouteGroup(string $name, string $label, string $routeName, string $webRoutePath, callable $callback): void
    {
        $webRouteContent = $this->filesystem->get($webRoutePath);

        $routeStub = <<<PHP

        Route::get('{$routeName}/data/list', [{$name}Controller::class, 'list'])->name('{$routeName}.list');
        Route::resource('{$routeName}', {$name}Controller::class);

    PHP;

        $pattern = '/Route::middleware\(\s*\[([^\]]*)\]\s*\)->group\(function\s*\(\)\s*{([\s\S]*?)^\s*}\);/m';
        if (preg_match($pattern, $webRouteContent, $matches, PREG_OFFSET_CAPTURE)) {
            $middlewareArray = $matches[1][0];
            $groupBody = $matches[2][0];

            if (strpos($middlewareArray, 'auth') !== false) {
                if (strpos($groupBody, "[App\Http\Controllers\\{$name}Controller::class") === false && strpos($groupBody, "{$name}Controller") === false) {
                    $insertPos = $matches[2][1] + strlen($groupBody);
                    $newContent = substr($webRouteContent, 0, $insertPos).$routeStub.substr($webRouteContent, $insertPos);
                    $this->filesystem->put($webRoutePath, $newContent);
                    $callback("Resource route for {$name} appended to routes/web.php.", 'info');
                } else {
                    $callback("Route for {$name} already exists in routes/web.php. Skipping append.", 'warn');
                }
            }
        }
    }
}
