<?php

namespace Laralib\L5scaffold\Tests;

use PHPUnit_Framework_TestCase as PHPUnit;

use \Illuminate\Support\Facades\Artisan;
use \Illuminate\Filesystem\Filesystem;
use \Illuminate\Support\Composer;
use Mockery;


class CommandTest extends PHPUnit
{
	protected $app;
	protected $filesystem;
	protected $folders;

	public function setUp()
	{
		parent::setUp();
		
		$this->prepareFolderStructure();

		$this->createApplication();

		$this->mountFolderStructure();
	}

	public function tearDown()
	{
		$this->unmontFolderStructure();
	}

	public function createApplication()
	{
		$this->app = require __DIR__.'/../vendor/laravel/laravel/bootstrap/app.php';
		
		$this->app->register('Laralib\L5scaffold\GeneratorsServiceProvider');

        $this->app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
	}

	public function prepareFolderStructure()
	{
		$this->filesystem = new Filesystem();

		 $this->folders = 
		[
			'app/Http/Controllers', 
			'database/seeds', 
			'database/migrations',
			'resources/views'
		];
	}

	public function mountFolderStructure()
	{
		foreach($this->folders as $folder)
		{
			$this->filesystem->makeDirectory($folder, 0777, true, true);
		}
	}

	public function unmontFolderStructure()
	{
		foreach ($this->folders as $folder) 
		{
			$this->filesystem->deleteDirectory(explode("/", $folder)[0]);
		}
	}

	public function testExecuteCommand()
	{
        Artisan::call('make:scaffold', 
		[
        	'name' => 'Tweet', 
        	'--schema' => 'title:string',
			'--validator' => 'title:required|unique:tweets,id',
        	'--no-interaction'
    	]);

		Artisan::call('make:scaffold',
		[
			'name' => 'Tweet2',
			'--schema' => 'title:string',
			'--localization' => 'title:required',
			'--lang' => 'fr',
			'--no-interaction'
		]);
	}
}