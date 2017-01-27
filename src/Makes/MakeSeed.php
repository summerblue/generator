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
        $path = $this->getPath($this->scaffoldCommandObj->getObjName('Name') . 'TableSeeder', 'seed');


        if ($this->files->exists($path))
        {
            return $this->scaffoldCommandObj->comment('x Seed');
        }

        $this->makeDirectory($path);
        $this->files->put($path, $this->compileSeedStub());
        $this->scaffoldCommandObj->info('+ Seed');
    }

    /**
     * Compile the seed stub.
     *
     * @return string
     */
    protected function compileSeedStub()
    {
        $stub = $this->files->get(substr(__DIR__,0, -5) . 'Stubs/seed.stub');

        $this->buildStub($this->scaffoldCommandObj->getMeta(), $stub);

        return $stub;
    }
}