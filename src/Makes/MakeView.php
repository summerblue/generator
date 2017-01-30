<?php
/**
 * Created by PhpStorm.
 * User: fernandobritofl
 * Date: 4/21/15
 * Time: 4:58 PM
 */

namespace Summerblue\Generator\Makes;

use Illuminate\Filesystem\Filesystem;
use Summerblue\Generator\Commands\ScaffoldMakeCommand;
use Summerblue\Generator\Migrations\SchemaParser;
use Summerblue\Generator\Migrations\SyntaxBuilder;

class MakeView
{
    use MakerTrait;

    /**
     * Store scaffold command.
     *
     * @var ScaffoldMakeCommand
     */
    protected $scaffoldCommandObj;

    /**
     * Store property of model
     *
     * @var array
     */
    protected $schemaArray = [];

    /**
     * Create a new instance.
     *
     * @param ScaffoldMakeCommand $scaffoldCommand
     * @param Filesystem $files
     * @param sting $viewName
     * @return void
     */
    public function __construct(ScaffoldMakeCommand $scaffoldCommand, Filesystem $files)
    {
        $this->files = $files;
        $this->scaffoldCommandObj = $scaffoldCommand;

        $this->start();
    }

    /**
     * Get all property of model
     *
     * @return void
     */
    protected function getSchemaArray()
    {
        // ToDo - schema is required?
        if($this->scaffoldCommandObj->option('schema') != null)
        {
            if ($schema = $this->scaffoldCommandObj->option('schema'))
            {
                return (new SchemaParser)->parse($schema);
            }
        }

        return [];
    }

    /**
     * Start make view.
     *
     * @return void
     */
    private function start()
    {
        $this->scaffoldCommandObj->line("\n--- Views ---");

        $viewsFiles = $this->getStubViews($this->scaffoldCommandObj->getMeta()['ui']);
        $destination = $this->getDestinationViews($this->scaffoldCommandObj->getMeta()['models']);
        $metas = $this->scaffoldCommandObj->getMeta();

        $metas = array_merge_recursive
        (
            $metas,
            [
                'form_fields_fillable' => $this->getFields($metas['ui'], 'fillable'),
                'form_fields_empty' => $this->getFields($metas['ui'], 'fillable'),
                'form_fields_show' => $this->getFields($metas['ui'], 'show'),
                'table_fields_header' => $this->getFields($metas['ui'], 'header'),
                'table_fields_content' => $this->getFields($metas['ui'], 'content'),
            ]
        );

        foreach ($viewsFiles as $viewFileBaseName => $viewFile)
        {
            $viewFileName = str_replace('.stub', '', $viewFileBaseName);
            $viewDestination = $destination . $viewFileName;

            if ($this->files->exists($viewDestination))
            {
                $this->scaffoldCommandObj->comment("   x $viewFileName");
                continue;
            }

            $stub = $this->files->get($viewFile);
            $stub = $this->buildStub($metas, $stub);
            
            $this->makeDirectory($viewDestination);
            $this->files->put($viewDestination, $stub);
            $this->scaffoldCommandObj->info("   + $viewFileName");
        }
    }
    
    protected function getFields($ui, $type)
    {
        $stubsFields = $this->getStubFields($ui, $type);
        $stubsFieldsAllow = array_keys($stubsFields);
        $schemas = $this->getSchemaArray();
        $metas = $this->scaffoldCommandObj->getMeta();

        $stubs = [];

        foreach ($schemas as $schema)
        {
            $variablesFromField = $this->getVariablesFromField($schema);
            $fieldType = $variablesFromField['field.type'];
            
            if(!in_array($fieldType, $stubsFieldsAllow))
            {
                $fieldType = 'default';
            }

            $stub = $stubsFields[$fieldType];
            $stub = $this->buildStub($variablesFromField, $stub);
            $stub = $this->buildStub($metas, $stub);

            $stubs[] = $stub;
        }

        return join(' ', $stubs);
    }

    private function getVariablesFromField($options)
    {
        $data = [];
     
        $data['field.name'] = $options['name'];
        $data['field.Name'] = ucwords($options['name']);
        $data['field.type'] = @$options['type'];
        $data['field.value.default'] = @$options['options']['default'];

        return $data;
    }
}
