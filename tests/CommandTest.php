<?php

namespace Summerblue\Generator\Tests;

use PHPUnit\Framework\TestCase as PHPUnit;

use \Illuminate\Support\Facades\Artisan;
use \Illuminate\Filesystem\Filesystem;
use Mockery;


class CommandTest extends PHPUnit
{
    protected $app;
    protected $filesystem;
    protected $folders;
    protected $files;

    public function setUp()
    {
        parent::setUp();

        $this->prepareFilesystem();

        $this->createApplication();

        $this->mountFolderStructure();
    }

    public function tearDown()
    {
        $this->cleanFilesystem();
    }

    public function createApplication()
    {
        $this->app = require __DIR__ . '/../vendor/laravel/laravel/bootstrap/app.php';

        $this->app->register('Summerblue\Generator\GeneratorsServiceProvider');

        $this->app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    }

    public function prepareFilesystem()
    {
        $this->filesystem = new Filesystem();

        $this->folders =
            [
                'routes',
                'app/Http/Controllers',
                'app/Models',
                'app/Providers',
                'database/seeds',
                'database/migrations',
                'database/factories',
                'resources/views',
            ];

        $this->files =
            [
                './database/factories/UserFactory.php',
                './database/seeds/DatabaseSeeder.php',
                './app/Models/Model.php',
                './app/Providers/AuthServiceProvider.php',
                './app/Providers/AppServiceProvider.php',
                './routes/web.php',
            ];

    }

    public function mountFolderStructure()
    {
        foreach ($this->folders as $folder) {
            $this->filesystem->makeDirectory($folder, 0777, true, true);
        }


        foreach ($this->files as $file) {
            $this->filesystem->put($file, '<?php');
        }
    }

    public function cleanFilesystem()
    {
        foreach ($this->folders as $folder) {
            $this->filesystem->deleteDirectory(explode("/", $folder)[0]);
        }
    }

    public function testExecuteCommand()
    {
        Artisan::call('make:scaffold',
            [
                'name' => 'Tweet',
                '--schema' => 'title:string',
                '--no-interaction'
            ]);

        Artisan::call('make:scaffold',
            [
                'name' => 'Tweet2',
                '--schema' => 'title:string',
                '--no-interaction'
            ]);
    }
}