<?php

namespace App\Packages\Console;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ControllerCommand extends Command
{
    protected $file;
    protected $signature   = 'lett:controller {name} {--extend=} {--model=ModelName}';
    protected $description = 'create a new lett controller';
    protected $type        = 'controller';

    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    public function handle()
    {
        $name = $this->qualifyClass($this->getNameInput());
        $path = $this->getPath($name);

        if ((!$this->hasOption('force') ||
            !$this->option('force')) &&
            $this->alreadyExists($this->getNameInput())) {
            $this->error($this->type . ' already exists!');
            return false;
        }

        $this->makeDirectory($path);
        $this->files->put($path, $this->buildClass($name));
        $this->info($this->type . ' created successfully.');
    }

    protected function getStub()
    {
        $stub = '/stubs/controller.stub';
        return __DIR__ . $stub;
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Http\Controllers';
    }

    protected function buildClass($name)
    {
        $controllerNamespace                                 = $this->getNamespace($name);
        $replace                                             = [];
        $replace["use {$controllerNamespace}\Controller;\n"] = '';
        return str_replace(
            array_keys($replace), array_values($replace), $this->buildClassParent($name)
        );
    }

    protected function buildClassParent($name)
    {
        $stub = $this->files->get($this->getStub());
        $this->replaceNamespace($stub, $name)->replaceClass($stub, $name)
            ->replaceExtendClass($stub, $this->option('extend'))->replaceModelClass($stub, $this->option('model'));
        return $stub;
    }

    protected function getOptions()
    {
        return [
            ['extend', 'i', InputOption::VALUE_NONE, 'extend desc.'],
            ['model', 'i', InputOption::VALUE_NONE, 'model desc.'],
        ];
    }

    protected function qualifyClass($name)
    {
        $name          = ltrim($name, '\\/');
        $rootNamespace = $this->rootNamespace();
        if (Str::startsWith($name, $rootNamespace)) {return $name;}
        $name = str_replace('/', '\\', $name);
        $temp = $this->getDefaultNamespace(trim($rootNamespace, '\\')) . '\\' . $name;
        return $this->qualifyClass($temp);
    }

    protected function alreadyExists($rawName)
    {
        $class = $this->qualifyClass($rawName);
        $path  = $this->getPath($class);
        return $this->files->exists($path);
    }

    protected function getPath($name)
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);
        return $this->laravel['path'] . '/' . str_replace('\\', '/', $name) . '.php';
    }

    protected function makeDirectory($path)
    {
        if (!$this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0755, true, true);
        }

        return $path;
    }

    protected function replaceNamespace(&$stub, $name)
    {
        $stub = str_replace(
            ['LettNamespace', 'LettRootNamespace'],
            [$this->getNamespace($name), $this->rootNamespace()],
            $stub
        );

        return $this;
    }

    protected function getNamespace($name)
    {
        return trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
    }

    protected function replaceClass(&$stub, $name)
    {
        $class = str_replace($this->getNamespace($name) . '\\', '', $name);
        $stub  = str_replace('LettClass', $class, $stub);
        return $this;
    }

    protected function replaceExtendClass(&$stub, $extend)
    {
        $class = str_replace($this->getNamespace($extend) . '\\', '', $extend) . 'Controller';
        $use   = count(explode('\\', $extend)) >= 2 ? '' : $this->qualifyClass($extend) . 'Controller';
        $stub  = str_replace(['LettExtendNamespace', 'LettExtendClass'], [$use, $class], $stub);
        return $this;
    }

    protected function replaceModelClass(&$stub, $model)
    {
        $use  = 'App\Models\\' . $model;
        $stub = str_replace(['LettModelNamespace', 'LettModel'], [$use, $model], $stub);
        return $this;
    }

    protected function getNameInput()
    {
        return trim($this->argument('name'));
    }

    protected function rootNamespace()
    {
        return $this->laravel->getNamespace();
    }

    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the class'],
        ];
    }

}
