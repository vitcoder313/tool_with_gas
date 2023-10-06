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
    public function createMigration(array $data)
    {
        // get name table
        $name = ucfirst(array_key_first($data));
        // check name table is update or create
        $name = 'Create'.$name.'Table';
        //
        $tableName = Str::snake(trim($name));
        $content = $this->createContentMigration(current($data));
        $stub = $this->getStub();
        $path = $this->getPath($tableName, storage_path('migrations'));
        fopen($path, 'w') or die('Unable to open file!');
        file_put_contents($path, $this->populateStub($stub, $tableName, $name, $content));
        return $path;
    }

    protected function createContentMigration(array $data) {
        $content = '';
        foreach ($data as $column=>$info) {
            $content .= '$table->'.$info['type'].'("'.$column .'"'. ($info['length'] ? ', '.$info['length'] : ''). ')';
            if (isset($info['nullable']) && $info['nullable']) {
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

    /**
     * Populate the place-holders in the migration stub.
     *
     * @param  string  $stub
     * @param  string|null  $table
     * @return string
     */
    protected function populateStub($stub, $table, $name, $content = '')
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

        if (!is_null($content)) {
            $stub = str_replace(
                ['{{ content }}', '{{content}}'],
                $content, $stub
            );
        }

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
