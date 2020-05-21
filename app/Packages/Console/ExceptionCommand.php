<?php

namespace App\Packages\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class ExceptionCommand extends Command
{
    protected $file;
    protected $signature   = 'lett:exception';
    protected $description = 'copy lett exception';
    protected $type        = 'exception';

    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    public function handle()
    {
        $path    = app_path('Exceptions/Handler.php');
        $stub    = __DIR__ . '/stubs/exception-handler.stub';
        $content = $this->files->get($stub);
        $this->files->put($path, $content);
        $this->info('file Exceptions/Handler.php created successfully.');
    }
}
