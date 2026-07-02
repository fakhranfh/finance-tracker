<?php

namespace App\Console\Commands\Generators;

use App\Console\Commands\Stubs\TailwindBladeCreateStubGenerator;
use App\Console\Commands\Stubs\TailwindBladeEditStubGenerator;
use App\Console\Commands\Stubs\TailwindBladeIndexStubGenerator;
use App\Console\Commands\Stubs\TailwindBladeShowStubGenerator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class BladeGenerator
{
    private Filesystem $filesystem;

    private TailwindBladeIndexStubGenerator $indexStubGenerator;

    private TailwindBladeCreateStubGenerator $createStubGenerator;

    private TailwindBladeEditStubGenerator $editStubGenerator;

    private TailwindBladeShowStubGenerator $showStubGenerator;

    public function __construct()
    {
        $this->filesystem = new Filesystem;
        $this->indexStubGenerator = new TailwindBladeIndexStubGenerator;
        $this->createStubGenerator = new TailwindBladeCreateStubGenerator;
        $this->editStubGenerator = new TailwindBladeEditStubGenerator;
        $this->showStubGenerator = new TailwindBladeShowStubGenerator;
    }

    public function generate(string $name, string $label, string $viewPath = 'app', array $columnInputTypes = [], array $filterDefinitions = [], ?callable $callback = null): void
    {
        $kebabCaseName = Str::kebab($name);
        $routeName = Str::kebab($label);
        $bladeDir = resource_path("views/{$viewPath}/{$kebabCaseName}");

        $indexBladePath = "{$bladeDir}/index.blade.php";
        $createBladePath = "{$bladeDir}/create.blade.php";
        $editBladePath = "{$bladeDir}/edit.blade.php";
        $showBladePath = "{$bladeDir}/show.blade.php";

        $this->filesystem->ensureDirectoryExists($bladeDir);

        // Generate Index Blade
        $indexContent = $this->indexStubGenerator->generate($name, $label, $filterDefinitions);
        $indexContent = str_replace(['LABEL', 'ROUTENAME'], [$label, $routeName], $indexContent);

        if (! $this->filesystem->exists($indexBladePath)) {
            $this->filesystem->put($indexBladePath, $indexContent);
            if (is_callable($callback)) {
                $callback("Blade index view created: {$indexBladePath}", 'info');
            }
        } else {
            if (is_callable($callback)) {
                $callback("Blade index view already exists: {$indexBladePath}", 'warn');
            }
        }

        // Generate Create Blade
        $createContent = $this->createStubGenerator->generate($name, $label, $columnInputTypes, $filterDefinitions);
        $createContent = str_replace(['LABEL', 'ROUTENAME'], [$label, $routeName], $createContent);

        if (! $this->filesystem->exists($createBladePath)) {
            $this->filesystem->put($createBladePath, $createContent);
            if (is_callable($callback)) {
                $callback("Blade create view created: {$createBladePath}", 'info');
            }
        } else {
            if (is_callable($callback)) {
                $callback("Blade create view already exists: {$createBladePath}", 'warn');
            }
        }

        // Generate Edit Blade
        $editContent = $this->editStubGenerator->generate($name, $label, $columnInputTypes, $filterDefinitions);
        $editContent = str_replace(['LABEL', 'ROUTENAME'], [$label, $routeName], $editContent);

        if (! $this->filesystem->exists($editBladePath)) {
            $this->filesystem->put($editBladePath, $editContent);
            if (is_callable($callback)) {
                $callback("Blade edit view created: {$editBladePath}", 'info');
            }
        } else {
            if (is_callable($callback)) {
                $callback("Blade edit view already exists: {$editBladePath}", 'warn');
            }
        }

        // Generate Show Blade
        $showContent = $this->showStubGenerator->generate($name, $label);
        $showContent = str_replace(['LABEL', 'ROUTENAME'], [$label, $routeName], $showContent);

        if (! $this->filesystem->exists($showBladePath)) {
            $this->filesystem->put($showBladePath, $showContent);
            if (is_callable($callback)) {
                $callback("Blade show view created: {$showBladePath}", 'info');
            }
        } else {
            if (is_callable($callback)) {
                $callback("Blade show view already exists: {$showBladePath}", 'warn');
            }
        }

        // Add sidebar button
        if (is_callable($callback)) {
            $this->addSidebarItem($routeName, $label, $callback);
        }
    }

    private function addSidebarItem(string $routeName, string $label, callable $callback): void
    {
        $configPath = config_path('sidebar.php');

        if (! $this->filesystem->exists($configPath)) {
            $callback("Sidebar config not found: {$configPath}", 'error');

            return;
        }

        $configContent = $this->filesystem->get($configPath);

        if (str_contains($configContent, "'{$routeName}.index'")) {
            $callback("Sidebar item for '{$label}' already exists", 'warn');

            return;
        }

        $icon = $this->getIconForRoute($routeName);
        $newItem = <<<PHP
            [
                'label' => '{$label}',
                'route' => '{$routeName}.index',
                'icon' => '{$icon}',
                'active_pattern' => '{$routeName}.*',
            ],
        PHP;

        $updatedContent = str_replace('];', "{$newItem}\n];", $configContent);

        $this->filesystem->put($configPath, $updatedContent);
        $callback("Sidebar item added for '{$label}'", 'info');
    }

    private function getIconForRoute(string $routeName): string
    {
        $iconMap = [
            'dashboard' => 'dashboard',
            'user' => 'person',
            'product' => 'shopping_cart',
            'order' => 'receipt',
            'category' => 'category',
            'setting' => 'settings',
            'report' => 'assessment',
            'profile' => 'account_circle',
        ];

        return $iconMap[$routeName] ?? 'folder';
    }
}
