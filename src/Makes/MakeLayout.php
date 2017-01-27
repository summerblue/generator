<?php
/**
 * Created by PhpStorm.
 * User: fernandobritofl
 * Date: 4/22/15
 * Time: 11:49 PM
 */

namespace Laralib\L5scaffold\Makes;

use Illuminate\Filesystem\Filesystem;
use Laralib\L5scaffold\Commands\ScaffoldMakeCommand;

class MakeLayout
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
     * Start make layout(view).
     *
     * @return void
     */
    protected function start()
    {
        $ui = $this->scaffoldCommandObj->getMeta()['ui'];
        $this->putViewLayout('Layout', "Stubs/views/$ui/layout.blade.php.stub", 'layout.blade.php');
        $this->putViewLayout('Error', "Stubs/views/$ui/error.blade.php.stub", 'error.blade.php');
    }


    /**
     * Write layout in path
     *
     * @param $path_resource
     * @return void
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function putViewLayout($name, $stub, $file)
    {
        $path_file = $this->getPathResource().$file;
        $path_stub = substr(__DIR__,0, -5) .$stub;

        if ($this->files->exists($path_file))
        {
            return $this->scaffoldCommandObj->comment("x $name (Skip)");
        }

        $html = $this->files->get($path_stub);
        $html = $this->buildStub($this->scaffoldCommandObj->getMeta(), $html);
        $this->files->put($path_file, $html);
        $this->scaffoldCommandObj->info("+ $name");
    }

    /**
     * Get the path to where we should store the view.
     *
     * @return string
     */
    protected function getPathResource()
    {
        return './resources/views/';
    }
}