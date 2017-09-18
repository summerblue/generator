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

class MakeMigration
{
    use MakerTrait;

    /**
     * Store scaffold command.
     *
     * @var ScaffoldMakeCommand
     */
    protected $scaffoldCommandObj;

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
     * Start make migration.
     *
     * @return void
     */
    protected function start(){
        $name = 'create_'.str_plural(strtolower( $this->scaffoldCommandObj->argument('name') )).'_table';
        $path = $this->getPath($name);

        if ( ! $this->classExists($name))
        {
            $this->makeDirectory($path);
            $this->files->put($path, $this->compileMigrationStub());
            return $this->scaffoldCommandObj->info('+ ' . $path);
        }
        return $this->scaffoldCommandObj->comment('x ' . $path);
    }

    /**
     * Get the path to where we should store the migration.
     *
     * @param  string $name
     * @return string
     */
    protected function getPath($name)
    {
        return './database/migrations/'.date('Y_m_d_His').'_'.$name.'.php';
    }

    /**
     * Compile the migration stub.
     *
     * @return string
     */
    protected function compileMigrationStub()
    {
        $stub = $this->files->get(substr(__DIR__,0, -5) . 'Stubs/migration.stub');

        $this->replaceSchema($stub);
        $this->buildStub($this->scaffoldCommandObj->getMeta(), $stub);

        return $stub;
    }

    /**
     * Replace the schema for the stub.
     *
     * @param  string $stub
     * @param string $type
     * @return $this
     */
    protected function replaceSchema(&$stub)
    {
        if ($schema = $this->scaffoldCommandObj->getMeta()['schema'])
        {
            $schema = (new SchemaParser)->parse($schema);
        }

        $schema = (new SyntaxBuilder)->create($schema, $this->scaffoldCommandObj->getMeta());
        $stub = str_replace(['{{schema_up}}', '{{schema_down}}'], $schema, $stub);
        
        return $this;
    }

    public function classExists($name)
    {
        $files = $this->files->allFiles('./database/migrations/');
        foreach ($files as $file) {
            if (strpos($file->getFilename(), $name) !== false) {
                return true;
            }
        }

        return false;
    }
}