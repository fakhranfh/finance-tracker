<?php

namespace App\Console\Commands\Stubs;

use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\Type;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ControllerStubGenerator
{
    public function generate(string $name, string $label, string $viewPath = 'app', array $filterDefinitions = []): string
    {
        $kebabCaseName = Str::kebab($name);
        $camelCaseName = Str::camel($name);
        $labelKebab = Str::kebab($label);

        $modelClass = "App\\Models\\$name";
        $foreignKeys = $this->getForeignKeys($modelClass);

        $foreignServiceImports = $this->generateForeignServiceImports($name, $foreignKeys);
        $foreignServiceProperties = $this->generateForeignServiceProperties($name, $foreignKeys);
        $foreignConstructorParams = $this->generateForeignConstructorParams($name, $foreignKeys);
        $foreignServiceAssignments = $this->generateForeignServiceAssignments($name, $foreignKeys);
        $foreignDataMethod = $this->generateForeignDataMethod($name, $foreignKeys);
        $filterOnlyKeys = $this->buildFilterOnlyKeys($filterDefinitions);
        $jsonFields = $this->buildJsonFields($filterDefinitions);

        return <<<PHP
<?php

namespace App\Http\Controllers;

use App\Services\\{$name}Service;

$foreignServiceImports
use App\Http\Requests\\{$name}\\Store{$name}Request;
use App\Http\Requests\\{$name}\\Update{$name}Request;
use Illuminate\Http\Request;

class {$name}Controller extends Controller
{
    protected \${$camelCaseName}Service;
    $foreignServiceProperties

    public function __construct(
        {$name}Service \${$camelCaseName}Service,
        $foreignConstructorParams
    )
    {
        \$this->{$camelCaseName}Service = \${$camelCaseName}Service;
        $foreignServiceAssignments
    }

    $foreignDataMethod

    public function index()
    {
        return view('{$viewPath}.{$kebabCaseName}.index');
    }

    public function list(Request \$request)
    {
        \$filters = \$request->only([{$filterOnlyKeys}]);
        \$items = \$this->{$camelCaseName}Service->get(\$filters);

        return response()->json([
            'data' => \$items->map(fn(\$item) => [
                'id' => \$item->id ?? '',
{$jsonFields}
                'actions' => [
                    'show' => route('{$labelKebab}.show', \$item->id),
                    'edit' => route('{$labelKebab}.edit', \$item->id),
                    'delete' => route('{$labelKebab}.destroy', \$item->id),
                ]
            ])->toArray()
        ]);
    }

    public function show(\$id)
    {
        \$item = \$this->{$camelCaseName}Service->find(\$id);
        \$foreignData = \$this->foreignData();

        return view('{$viewPath}.{$kebabCaseName}.show', [
            'item' => \$item,
        ] + \$foreignData);
    }

    public function create()
    {
        return view('{$viewPath}.{$kebabCaseName}.create', \$this->foreignData());
    }

    public function store(Store{$name}Request \$request)
    {
        \$item = \$this->{$camelCaseName}Service->create(\$request->validated());
        return redirect()->route('{$labelKebab}.show', \$item)->with('success', __('{$label} created successfully.'));
    }

    public function edit(\$id)
    {
        \$item = \$this->{$camelCaseName}Service->find(\$id);
        \$foreignData = \$this->foreignData();

        return view('{$viewPath}.{$kebabCaseName}.edit', [
            'item' => \$item,
        ] + \$foreignData);
    }

    public function update(Update{$name}Request \$request, \$id)
    {
        \$this->{$camelCaseName}Service->update(\$id, \$request->validated());
        return redirect()->route('{$labelKebab}.show', \$id)->with('success', __('{$label} updated successfully.'));
    }

    public function destroy(\$id)
    {
        \$this->{$camelCaseName}Service->delete(\$id);

        if (request()->expectsJson()) {
            return response()->json(['message' => __('Item deleted successfully.')]);
        }

        return redirect()->route('{$labelKebab}.index')->with('success', __('{$label} deleted successfully.'));
    }
}
PHP;
    }

    private function getForeignKeys(string $modelClass): array
    {
        $foreignKeys = [];
        if (class_exists($modelClass)) {
            try {
                $model = new $modelClass;
                $table = $model->getTable();
                $connection = Schema::getConnection();

                if (! method_exists($connection, 'getDoctrineConnection')) {
                    return $foreignKeys;
                }

                $doctrineConn = $connection->getDoctrineConnection();
                if (! Type::hasType('enum')) {
                    Type::addType('enum', StringType::class);
                }
                $platform = $doctrineConn->getDatabasePlatform();
                if (method_exists($platform, 'registerDoctrineTypeMapping')) {
                    $platform->registerDoctrineTypeMapping('enum', 'string');
                }
                $sm = $connection->getDoctrineSchemaManager();
                if (! Schema::hasTable($table)) {
                    return $foreignKeys;
                }
                $doctrineTable = $sm->introspectTable($table);
                foreach ($doctrineTable->getForeignKeys() as $fk) {
                    foreach ($fk->getLocalColumns() as $localCol) {
                        $foreignKeys[$localCol] = [
                            'table' => $fk->getForeignTableName(),
                            'column' => $fk->getForeignColumns()[0],
                        ];
                    }
                }
            } catch (\Exception $e) {
                return $foreignKeys;
            }
        }

        return $foreignKeys;
    }

    private function generateForeignServiceImports(string $name, array $foreignKeys): string
    {
        if (empty($foreignKeys)) {
            return '';
        }

        $imports = '';
        $imported = [];
        foreach ($foreignKeys as $fk) {
            $relatedTable = $fk['table'];
            $relatedModel = Str::studly(Str::singular($relatedTable));
            $relatedService = "App\\Services\\{$relatedModel}Service";
            if (! in_array($relatedService, $imported) && $relatedModel !== $name) {
                $imports .= "use {$relatedService};\n";
                $imported[] = $relatedService;
            }
        }

        return $imports;
    }

    private function generateForeignServiceProperties(string $name, array $foreignKeys): string
    {
        if (empty($foreignKeys)) {
            return '';
        }

        $properties = '';
        $imported = [];
        $fkCount = count($foreignKeys);
        $i = 0;
        foreach ($foreignKeys as $fk) {
            $i++;
            $relatedTable = $fk['table'];
            $relatedModel = Str::studly(Str::singular($relatedTable));
            if ($relatedModel !== $name && ! in_array($relatedModel, $imported)) {
                $camelRelated = Str::camel($relatedModel);
                if ($i === $fkCount) {
                    $properties .= "protected \${$camelRelated}Service;";
                } else {
                    $properties .= "protected \${$camelRelated}Service;\n    ";
                }
                $imported[] = $relatedModel;
            }
        }

        return $properties;
    }

    private function generateForeignConstructorParams(string $name, array $foreignKeys): string
    {
        if (empty($foreignKeys)) {
            return '';
        }

        $params = '';
        $imported = [];
        $fkCount = count($foreignKeys);
        $i = 0;
        foreach ($foreignKeys as $fk) {
            $i++;
            $relatedTable = $fk['table'];
            $relatedModel = Str::studly(Str::singular($relatedTable));
            if ($relatedModel !== $name && ! in_array($relatedModel, $imported)) {
                $camelRelated = Str::camel($relatedModel);
                if ($i === $fkCount) {
                    $params .= "{$relatedModel}Service \${$camelRelated}Service";
                } else {
                    $params .= "{$relatedModel}Service \${$camelRelated}Service,\n        ";
                }
                $imported[] = $relatedModel;
            }
        }

        return rtrim($params, ', ');
    }

    private function generateForeignServiceAssignments(string $name, array $foreignKeys): string
    {
        if (empty($foreignKeys)) {
            return '';
        }

        $assignments = '';
        $imported = [];
        $fkCount = count($foreignKeys);
        $i = 0;
        foreach ($foreignKeys as $fk) {
            $i++;
            $relatedTable = $fk['table'];
            $relatedModel = Str::studly(Str::singular($relatedTable));
            if ($relatedModel !== $name && ! in_array($relatedModel, $imported)) {
                $camelRelated = Str::camel($relatedModel);
                if ($i === $fkCount) {
                    $assignments .= "\$this->{$camelRelated}Service = \${$camelRelated}Service;";
                } else {
                    $assignments .= "\$this->{$camelRelated}Service = \${$camelRelated}Service;\n        ";
                }
                $imported[] = $relatedModel;
            }
        }

        return $assignments;
    }

    private function buildJsonFields(array $filterDefinitions): string
    {
        $lines = [];

        foreach ($filterDefinitions as $filter) {
            if ($filter['key'] === 'created' && $filter['type'] === 'datetime') {
                $lines[] = "                'created_at' => \$item->created_at?->format('Y-m-d H:i:s') ?? '',";
            } elseif ($filter['type'] === 'datetime') {
                $lines[] = "                '{$filter['key']}' => \$item->{$filter['key']}?->format('Y-m-d H:i:s') ?? '',";
            } else {
                $lines[] = "                '{$filter['key']}' => \$item->{$filter['key']} ?? '',";
            }
        }

        return implode("\n", $lines);
    }

    private function buildFilterOnlyKeys(array $filterDefinitions): string
    {
        $keys = [];

        foreach ($filterDefinitions as $filter) {
            if ($filter['type'] === 'text' || $filter['type'] === 'enum') {
                $keys[] = "'{$filter['key']}'";
            } elseif ($filter['type'] === 'datetime') {
                $keys[] = "'{$filter['key']}_from'";
                $keys[] = "'{$filter['key']}_to'";
            }
        }

        return implode(', ', $keys);
    }

    private function generateForeignDataMethod(string $name, array $foreignKeys): string
    {
        if (empty($foreignKeys)) {
            return "private function foreignData()\n    {\n        return [];\n    }";
        }

        $foreignDataLines = [];
        $imported = [];

        foreach ($foreignKeys as $localCol => $fk) {
            $relatedTable = $fk['table'];
            $relatedModel = Str::studly(Str::singular($relatedTable));
            $camelRelated = Str::camel($relatedModel);
            if (! in_array($relatedModel, $imported) && $relatedModel !== $name) {
                $foreignDataLines[] = "'{$relatedTable}' => \$this->{$camelRelated}Service->getAll()";
                $imported[] = $relatedModel;
            }
        }

        return "private function foreignData()\n    {\n        return [\n            ".implode(",\n            ", $foreignDataLines)."\n        ];\n    }";
    }
}
