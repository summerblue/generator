<?php
namespace Summerblue\Generator\Makes;

use Illuminate\Filesystem\Filesystem;
use Summerblue\Generator\Commands\ScaffoldMakeCommand;
use Summerblue\Generator\Validators\SchemaParser as ValidatorParser;
use Summerblue\Generator\Validators\SyntaxBuilder as ValidatorSyntax;

class MakeModelObserver
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
        $name = $this->scaffoldCommandObj->getObjName('Name');
        $observer_name = $name . 'Observer';
        $this->makeObserver($observer_name, 'observer');

        $this->registerObserver($name, $observer_name);
    }

    protected function makeObserver($name, $stubname)
    {
        $path = $this->getPath($name, 'observer');
        $userpath = $this->getPath('UserObserver', 'observer');
        $this->makeDirectory($path);

        // User Observer
        if ( ! $this->files->exists($userpath))
        {
            $this->files->put($userpath, $this->compileStub('observer_user'));
            $this->scaffoldCommandObj->comment("+ $userpath" . ' (Skipped)');
        }

        if ($this->files->exists($path))
        {
            return $this->scaffoldCommandObj->comment("x $path" . ' (Skipped)');
        }

        $this->files->put($path, $this->compileStub($stubname));

        $this->scaffoldCommandObj->info('+ ' . $path);
    }


    protected function registerObserver($model, $observer_name)
    {
        $path = './app/Providers/AppServiceProvider.php';
        $content = $this->files->get($path);

        if (strpos($content, $observer_name) === false) {

            // Using UserOberser as anchor
            if (strpos($content, 'App\Models\User') === false) {
                $content = str_replace(
                "public function boot()
    {",
                "public function boot()\n\t{\n\t\t\App\Models\User::observe(\App\Observers\UserObserver::class);\n",
                $content
                );
            }

            $content = str_replace(
                'App\Models\User::observe(\App\Observers\UserObserver::class);',
                "App\Models\User::observe(\App\Observers\UserObserver::class);\n\t\t\App\Models\\$model::observe(\App\Observers\\$observer_name::class);",
                $content
                );
            $this->files->put($path, $content);

            return $this->scaffoldCommandObj->info('+ ' . $path . ' (Updated)');
        }

        return $this->scaffoldCommandObj->comment("x " . $path . ' (Skipped)');
    }

}