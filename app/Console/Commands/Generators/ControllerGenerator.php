<?php

namespace App\Console\Commands\Generators;

use App\Console\Commands\Stubs\ControllerStubGenerator;
use Illuminate\Filesystem\Filesystem;

class ControllerGenerator
{
    private Filesystem $filesystem;

    private ControllerStubGenerator $stubGenerator;

    public function __construct()
    {
        $this->filesystem = new Filesystem;
        $this->stubGenerator = new ControllerStubGenerator;
    }

    public function generate(string $name, string $label, string $viewPath = 'app', array $filterDefinitions = [], ?callable $callback = null): void
    {
        $controllerPath = app_path("Http/Controllers/{$name}Controller.php");

        $this->filesystem->ensureDirectoryExists(app_path('Http/Controllers'));

        if (! $this->filesystem->exists($controllerPath)) {
            $content = $this->stubGenerator->generate($name, $label, $viewPath, $filterDefinitions);
            $this->filesystem->put($controllerPath, $content);
            if (is_callable($callback)) {
                $callback("Controller created: {$controllerPath}", 'info');
            }
        } else {
            if (is_callable($callback)) {
                $callback("Controller already exists: {$controllerPath}", 'warn');
            }
        }
    }
}
