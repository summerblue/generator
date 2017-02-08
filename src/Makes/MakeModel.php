<?php
/**
 * Created by PhpStorm.
 * User: fernandobritofl
 * Date: 4/22/15
 * Time: 10:34 PM
 */

namespace Summerblue\Generator\Makes;

use Illuminate\Filesystem\Filesystem;
use Summerblue\Generator\Commands\ScaffoldMakeCommand;
use Summerblue\Generator\Migrations\SchemaParser;
use Summerblue\Generator\Migrations\SyntaxBuilder;

class MakeModel
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
     * Start make controller.
     *
     * @return void
     */
    private function start()
    {
        $name = $this->scaffoldCommandObj->getObjName('Name');
        $path = $this->getPath($name, 'model');

        $this->createBaseModelIfNotExists();

        if ($this->files->exists($path))
        {
            return $this->scaffoldCommandObj->comment("x $path");
        }

        $this->files->put($path, $this->compileModelStub());

        $this->scaffoldCommandObj->info('+ ' . $path);
    }

    /**
     * Compile the migration stub.
     *
     * @return string
     */
    protected function compileModelStub()
    {
        $stub = $this->files->get(substr(__DIR__,0, -5) . 'Stubs/model.stub');

        $this->buildStub($this->scaffoldCommandObj->getMeta(), $stub);
        $this->buildFillable($stub);

        return $stub;
    }

    /**
     * Build stub replacing the variable template.
     *
     * @return string
     */
    protected function buildFillable(&$stub)
    {
        $schemaArray = [];

        $schema = $this->scaffoldCommandObj->getMeta()['schema'];

        if ($schema)
        {
            $items = (new SchemaParser)->parse($schema);
            foreach($items as $item)
            {
                $schemaArray[] = "'{$item['name']}'";
            }

            $schemaArray = join(", ", $schemaArray);
        }

        $stub = str_replace('{{fillable}}', $schemaArray, $stub);

        return $this;
    }

    protected function createBaseModelIfNotExists()
    {
        $base_model_path = $this->getPath("Model", 'model');
        if (!$this->files->exists($base_model_path))
        {
            $this->makeDirectory($base_model_path);
            $this->files->put($base_model_path, $this->compileBaseModelStub());
            return $this->scaffoldCommandObj->info("+ $base_model_path". ' (Updated)');
        }

        return $this->scaffoldCommandObj->comment("x $base_model_path" . ' (Skipped)');
    }

    protected function compileBaseModelStub()
    {
        $stub = $this->files->get(substr(__DIR__,0, -5) . 'Stubs/base_model.stub');

        $this->buildStub($this->scaffoldCommandObj->getMeta(), $stub);
        $this->buildFillable($stub);

        return $stub;
    }
}