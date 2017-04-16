<?php
namespace Summerblue\Generator\Makes;

use Illuminate\Filesystem\Filesystem;
use Summerblue\Generator\Commands\ScaffoldMakeCommand;
use Summerblue\Generator\Validators\SchemaParser as ValidatorParser;
use Summerblue\Generator\Validators\SyntaxBuilder as ValidatorSyntax;

class MakePolicy
{
    use MakerTrait;

    /**
     * Store name from Model
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
    function __construct(ScaffoldMakeCommand $scaffoldCommand, Filesystem $files)
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
        $model = $this->scaffoldCommandObj->getObjName('Name');
        $policy_name = $model . 'Policy';
        $this->makePolicy('Policy', 'base_policy');
        $this->makePolicy($policy_name, 'policy');

        $this->registerPolicy($model, $policy_name);
    }

    protected function makePolicy($name, $stubname)
    {
        $path = $this->getPath($name, 'policy');

        if ($this->files->exists($path))
        {
            return $this->scaffoldCommandObj->comment("x " . $path);
        }

        $this->makeDirectory($path);

        $this->files->put($path, $this->compileStub($stubname));

        $this->scaffoldCommandObj->info('+ ' . $path);
    }

    protected function compileStub($filename)
    {
        $stub = $this->files->get(substr(__DIR__,0, -5) . 'Stubs/'.$filename.'.stub');

        $this->buildStub($this->scaffoldCommandObj->getMeta(), $stub);
        // $this->replaceValidator($stub);

        return $stub;
    }

    protected function registerPolicy($model, $policy_name)
    {
        $path = './app/Providers/AuthServiceProvider.php';
        $content = $this->files->get($path);

        if (strpos($content, $policy_name) === false) {
            $content = str_replace(
                'policies = [',
                "policies = [\n\t\t \App\Models\\$model::class => \App\Policies\\$policy_name::class,",
                $content
                );
            $this->files->put($path, $content);

            return $this->scaffoldCommandObj->info('+ ' . $path . ' (Updated)');
        }

        return $this->scaffoldCommandObj->comment("x " . $path . ' (Skipped)');
    }


    // /**
    //  * Replace validator in the controller stub.
    //  *
    //  * @return $this
    //  */
    // private function replaceValidator(&$stub)
    // {
    //     if($schema = $this->scaffoldCommandObj->option('validator')){
    //         $schema = (new ValidatorParser)->parse($schema);
    //     }

    //     $schema = (new ValidatorSyntax)->create($schema, $this->scaffoldCommandObj->getMeta(), 'validation');
    //     $stub = str_replace('{{validation_fields}}', $schema, $stub);

    //     return $this;
    // }


}