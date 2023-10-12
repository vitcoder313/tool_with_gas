<?php

namespace App\Http\Services;

use Illuminate\Database\Console\Migrations\BaseCommand;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MigrationService extends BaseCommand
{
    protected Filesystem $files;

    public function __construct()
    {
        parent::__construct();
        $this->files = new Filesystem();
    }

    /**
     * Create a new migration at the given path.
     *
     * @param  string  $name
     * @param  string|null  $table
     * @param  bool  $create
     * @return string
     *
     * @throws \Exception
     */
    public function createMigration(array $data): array
    {
        // get name table
        $name = ucfirst($data['tableName']);
        $dataTable = $data['data'];
        // check name table is update or create
        $name = 'Create'.$name.'Table';
        $tableName = Str::snake(trim($name));
        $content = $this->createContentMigration($dataTable);
        $stub = $this->getStub();
//        $path = $this->getPath($tableName, storage_path('migrations'));
//        fopen($path, 'w') or die('Unable to open file!');
//        file_put_contents($path, $contentFileMigration); // write content to file
        return [
            'name' => $this->getDatePrefix().'_'.$tableName.'.php',
            'content' => $this->populateStub($stub, $tableName, $name, $content)
        ];
    }

    protected function createContentMigration(array $data) {
        $content = '';
        $framework = 'laravel';
        // check framework is laravel or lumen
        $data = array_filter($data, function($item) {
            return $item['name'] !== 'id' && $item['name'] !== 'created_at' && $item['name'] !== 'updated_at';
        });

        foreach ($data as $column=>$info) {
            $content = $this->contentNameAndTypeColumn($content, $info['name'], $info['type'], $info['length'] ?? null);
            if (isset($info['isNull']) && !$info['isNull']) {
                $content .= '->nullable()';
            }
            if (isset($info['unique']) && $info['unique']) {
                $content .= '->unique()';
            }
            if (isset($info['default']) && !is_null($info['default'])) {
                $content .= '->default('.$info['default'].')';
            }
            if (isset($info['comment'])) {
                $content .= '->comment("'.$info['comment'].'")';
            }
            $content .= ';'.PHP_EOL;
        }
        return $content;
    }

    function contentNameAndTypeColumn(string $content, string $name, string $type, ?int $length, $framework = 'laravel'): string
    {
        switch ($type) {
            case 'varchar':
                $type = 'string';
                break;
            case 'tinyint':
                $type = 'tinyInteger';
                break;
            case 'bigint':
                $type = 'bigInteger';
                break;
        }
        $content .= '$table->'.$type.'("'.$name.'"'. ($length ? ', '.$length : ''). ')';
        return $content;
    }

    /**
     * Populate the place-holders in the migration stub.
     *
     * @param string $stub
     * @param string $table
     * @param string $name
     * @return string
     */
    protected function populateStub(string $stub, string $table, string $name, $content = '')
    {
        // Here we will replace the table place-holders with the table specified by
        // the developer, which is useful for quickly creating a tables creation
        // or update migration from the console instead of typing it manually.
        if (!is_null($table)) {
            $stub = str_replace(
                ['DummyTable', '{{ table }}', '{{table}}'],
                $table, $stub
            );
        }

        if (!is_null($name)) {
            $stub = str_replace(
                ['{{ name }}', '{{name}}'],
                $name, $stub
            );
        }

        $stub = str_replace(
            ['{{ content }}', '{{content}}'],
            $content, $stub
        );

        return $stub;
    }

    protected function getClassMigration(string $name)
    {
        return 'class '.$this->getClassName($name).' extends Migration';
    }

    protected function getMigrationPath()
    {
        if (! is_null($targetPath = $this->input->getOption('path'))) {
            return ! $this->usingRealPath()
                ? $this->laravel->basePath().'/'.$targetPath
                : $targetPath;
        }

        return parent::getMigrationPath();
    }

    protected function getStub(): string
    {
        $stub = $this->stubPath().'/migration.create.stub';

        return $this->files->get($stub);
    }

    /**
     * Get the full path to the migration.
     *
     * @param  string  $name
     * @param  string  $path
     * @return string
     */
    protected function getPath($name, $path): string
    {
        return $path.'/'.$this->getDatePrefix().'_'.$name.'.php';
    }

    /**
     * Get the date prefix for the migration.
     *
     * @return string
     */
    protected function getDatePrefix(): string
    {
        return date('Y_m_d_His');
    }

    public function stubPath()
    {
        return __DIR__.'/stubs';
    }
}
