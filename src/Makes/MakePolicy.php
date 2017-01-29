<?php
namespace Laralib\L5scaffold\Makes;

use Illuminate\Console\AppNamespaceDetectorTrait;
use Illuminate\Filesystem\Filesystem;
use Laralib\L5scaffold\Commands\ScaffoldMakeCommand;
use Laralib\L5scaffold\Validators\SchemaParser as ValidatorParser;
use Laralib\L5scaffold\Validators\SyntaxBuilder as ValidatorSyntax;

class MakePolicy
{
    use AppNamespaceDetectorTrait, MakerTrait;

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
            return $this->scaffoldCommandObj->comment("x $name");
        }

        $this->makeDirectory($path);

        $this->files->put($path, $this->compileStub($stubname));

        $this->scaffoldCommandObj->info('+ ' . $name);
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
                'as ServiceProvider;', 
                "as ServiceProvider;\nuse App\Policies\\$policy_name;\nuse App\Models\\$model;",
                $content
                );
            $content = str_replace(
                'policies = [',
                "policies = [\n\t\t $model::class => $policy_name::class,",
                $content
                );
            $this->files->put($path, $content);

            return $this->scaffoldCommandObj->info('+ Policy Register successfully.');
        }
        
        return $this->scaffoldCommandObj->comment("x Policy Register Skipped.");
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