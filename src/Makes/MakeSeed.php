<?php
/**
 * Created by PhpStorm.
 * User: fernandobritofl
 * Date: 4/22/15
 * Time: 10:34 PM
 */

namespace Laralib\L5scaffold\Makes;

use Illuminate\Filesystem\Filesystem;
use Laralib\L5scaffold\Commands\ScaffoldMakeCommand;

class MakeSeed
{
    use MakerTrait;

    /**
     * Create a new instance.
     *
     * @param ScaffoldMakeCommand $scaffoldCommand
     * @param Filesystem $files
     * @return void
     */
    public function __construct(ScaffoldMakeCommand $scaffoldCommand, Filesystem $files)
    {
        $this->files = $files;
        $this->scaffoldCommandObj = $scaffoldCommand;

        $this->start();
    }

    /**
     * Start make seed.
     *
     * @return void
     */
    protected function start()
    {
        $this->generateFactory();
        $this->generateSeed();
    }

    protected function generateFactory()
    {
        $name = $this->scaffoldCommandObj->getObjName('Name');
        $path = $this->getPath($name, 'factory');

        if (strpos($this->files->get($path), "App\Models\\$name") === false) {
            $this->files->append($path, $this->compileStub('factory'));
            return $this->scaffoldCommandObj->info("+ $path");
        }
        
        return $this->scaffoldCommandObj->comment("x $path");
    }

    protected function generateSeed()
    {
        $path = $this->getPath($this->scaffoldCommandObj->getObjName('Name') . 'TableSeeder', 'seed');

        if ($this->files->exists($path))
        {
            return $this->scaffoldCommandObj->comment('x ' . $path);
        }

        $this->makeDirectory($path);
        $this->files->put($path, $this->compileStub('seed'));
        $this->scaffoldCommandObj->info('+ ' . $path);
    }

}