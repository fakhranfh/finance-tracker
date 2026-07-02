<?php

namespace App\Console\Commands\Helpers;

use Illuminate\Support\Facades\Schema;

class SchemaHelper
{
    public static function getTableColumns(string $name): array
    {
        $modelClass = "App\\Models\\$name";
        if (!class_exists($modelClass)) {
            return [];
        }
        $model = new $modelClass;
        $table = $model->getTable();
        if (!Schema::hasTable($table)) {
            return [];
        }
        $columns = Schema::getColumnListing($table);
        $types = [];
        foreach ($columns as $col) {
            $types[$col] = Schema::getColumnType($table, $col);
        }
        return $types;
    }
}
