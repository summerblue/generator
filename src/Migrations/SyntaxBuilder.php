<?php

namespace Summerblue\Generator\Migrations;

use Summerblue\Generator\GeneratorException;


/**
 * Class SyntaxBuilder with modifications by Fernando
 * @package Summerblue\Generator\Migrations
 * @author Jeffrey Way <jeffrey@jeffrey-way.com>
 */
class SyntaxBuilder
{

    /**
     * A template to be inserted.
     *
     * @var string
     */
    private $template;

    /**
     * @var bool
     */
    protected $illuminate = false;

    /**
     * Enable/Disable use of Illuminate/Html form facades
     *
     * @param $value
     */
    public function setIllumination($value) {
        $this->illuminate = $value;
    }

    /**
     * Create the PHP syntax for the given schema.
     *
     * @param  array $schema
     * @param  array $meta
     * @param  string $type
     * @param  bool $illuminate
     * @return string
     * @throws GeneratorException
     * @throws \Exception
     */
    public function create($schema, $meta, $type = "migration", $illuminate = false)
    {
        $this->setIllumination($illuminate);

        if ($type == "migration") {

            $up = $this->createSchemaForUpMethod($schema, $meta);
            $down = $this->createSchemaForDownMethod($schema, $meta);
            return compact('up', 'down');


        } else if ($type == "controller") {

            $fieldsc = $this->createSchemaForControllerMethod($schema, $meta);
            return $fieldsc;


        } else if ($type == "view-index-header") {

            $fieldsc = $this->createSchemaForViewMethod($schema, $meta, 'index-header');
            return $fieldsc;

        } else if ($type == "view-index-content") {

            $fieldsc = $this->createSchemaForViewMethod($schema, $meta, 'index-content');
            return $fieldsc;

        } else if ($type == "view-show-content") {

            $fieldsc = $this->createSchemaForViewMethod($schema, $meta, 'show-content');
            return $fieldsc;

        } else if ($type == "view-edit-content") {

            $fieldsc = $this->createSchemaForViewMethod($schema, $meta, 'edit-content');
            return $fieldsc;

        } else if ($type == "view-create-content") {

            $fieldsc = $this->createSchemaForViewMethod($schema, $meta, 'create-content');
            return $fieldsc;

        } else {
            throw new \Exception("Type not found in syntaxBuilder");
        }
    }

    /**
     * Create the schema for the "up" method.
     *
     * @param  string $schema
     * @param  array $meta
     * @return string
     * @throws GeneratorException
     */
    private function createSchemaForUpMethod($schema, $meta)
    {
        //dd($schema);
        $fields = $this->constructSchema($schema);


        if ($meta['action'] == 'create') {
            return $this->insert($fields)->into($this->getCreateSchemaWrapper());
        }

        if ($meta['action'] == 'add') {
            return $this->insert($fields)->into($this->getChangeSchemaWrapper());
        }

        if ($meta['action'] == 'remove') {
            $fields = $this->constructSchema($schema, 'Drop');

            return $this->insert($fields)->into($this->getChangeSchemaWrapper());
        }

        // Otherwise, we have no idea how to proceed.
        throw new GeneratorException;
    }


    /**
     * Construct the syntax for a down field.
     *
     * @param  array $schema
     * @param  array $meta
     * @return string
     * @throws GeneratorException
     */
    private function createSchemaForDownMethod($schema, $meta)
    {
        // If the user created a table, then for the down
        // method, we should drop it.
        if ($meta['action'] == 'create') {
            return sprintf("Schema::drop('%s');", $meta['table']);
        }

        // If the user added columns to a table, then for
        // the down method, we should remove them.
        if ($meta['action'] == 'add') {
            $fields = $this->constructSchema($schema, 'Drop');

            return $this->insert($fields)->into($this->getChangeSchemaWrapper());
        }

        // If the user removed columns from a table, then for
        // the down method, we should add them back on.
        if ($meta['action'] == 'remove') {
            $fields = $this->constructSchema($schema);

            return $this->insert($fields)->into($this->getChangeSchemaWrapper());
        }

        // Otherwise, we have no idea how to proceed.
        throw new GeneratorException;
    }

    /**
     * Store the given template, to be inserted somewhere.
     *
     * @param  string $template
     * @return $this
     */
    private function insert($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Get the stored template, and insert into the given wrapper.
     *
     * @param  string $wrapper
     * @param  string $placeholder
     * @return mixed
     */
    private function into($wrapper, $placeholder = 'schema_up')
    {
        return str_replace('{{' . $placeholder . '}}', $this->template, $wrapper);
    }

    /**
     * Get the wrapper template for a "create" action.
     *
     * @return string
     */
    private function getCreateSchemaWrapper()
    {
        return file_get_contents(__DIR__ . '/../Stubs/schema-create.stub');
    }

    /**
     * Get the wrapper template for an "add" action.
     *
     * @return string
     */
    private function getChangeSchemaWrapper()
    {
        return file_get_contents(__DIR__ . '/../Stubs/schema-change.stub');
    }

    /**
     * Construct the schema fields.
     *
     * @param  array $schema
     * @param  string $direction
     * @return array
     */
    private function constructSchema($schema, $direction = 'Add')
    {
        if (!$schema) return '';

        $fields = array_map(function ($field) use ($direction) {
            $method = "{$direction}Column";
            return $this->$method($field);
        }, $schema);


        return implode("\n" . str_repeat(' ', 12), $fields);
    }


    /**
     * Construct the syntax to add a column.
     *
     * @param  string $field
     * @param string $type
     * @param $meta
     * @return string
     */
    private function addColumn($field, $type = "migration", $meta = "")
    {


        if ($type == 'migration') {

            $syntax = sprintf("\$table->%s('%s')", $field['type'], $field['name']);

            // If there are arguments for the schema type, like decimal('amount', 5, 2)
            // then we have to remember to work those in.
            if ($field['arguments']) {
                $syntax = substr($syntax, 0, -1) . ', ';

                $syntax .= implode(', ', $field['arguments']) . ')';
            }

            foreach ($field['options'] as $method => $value) {
                $syntax .= sprintf("->%s(%s)", $method, $value === true ? '' : $value);
            }

            $syntax .= ';';


        } elseif ($type == 'view-index-header') {

            // Fields to index view
            $syntax = sprintf("<th>%s", strtoupper($field['name']));
            $syntax .= '</th>';

        } elseif ($type == 'view-index-content') {

            // Fields to index view
            $syntax = sprintf("<td>{{\$%s->%s", $meta['var_name'], strtolower($field['name']));
            $syntax .= '}}</td>';

        } elseif ($type == 'view-show-content') {

            // Fields to show view
            $syntax = sprintf("<div class=\"form-group\">\n" .
                str_repeat(' ', 21) . "<label for=\"%s\">%s</label>\n" .
                str_repeat(' ', 21) . "<p class=\"form-control-static\">{{\$%s->%s}}</p>\n" .
                str_repeat(' ', 16) . "</div>", strtolower($field['name']), strtoupper($field['name']), $meta['var_name'], strtolower($field['name']));


        } elseif ($type == 'view-edit-content') {
            $syntax = $this->buildField($field, $type, $meta['var_name']);
        } elseif ($type == 'view-create-content') {
            $syntax = $this->buildField($field, $type, $meta['var_name'], false);
        } else {
            // Fields to controller
            $syntax = sprintf("\$%s->%s = \$request->input(\"%s", $meta['var_name'], $field['name'], $field['name']);
            $syntax .= '");';
        }


        return $syntax;
    }

    /**
     * Build form field with validation using Illuminate/Html Form facade or pure HTML
     *
     * @param $field
     * @param $variable
     * @param bool $value
     * @return string
     */
    private function buildField($field, $type, $variable, $value = true)
    {
        $column = strtolower($field['name']);
        $title = ucfirst($field['name']);

        if ($value === true) {
            $value = '$' . $variable . '->' . $column;
        } else {
            $value = 'old("'.$column.'")';
        }

        $syntax = [];

        switch($type) {
            case 'string':
            default:
                $input = 'text';
                break;
            case 'text':
                $input = 'textarea';
                break;
        }

        $syntax[] = '<div class="form-group @if($errors->has('."'". $column . "'".')) has-error @endif">';
        $syntax[] = '   <label for="' . $column . '-field">' . $title . '</label>';

        if($this->illuminate) {
            $syntax[] = '   {!! Form::' . $input . '("' . $column . '", ' . $value . ', array("class" => "form-control", "id" => "' . $column . '-field")) !!}';
        } else {
            $syntax[] = $this->htmlField($column, $variable, $field, $type);
        }

        $syntax[] = '   @if($errors->has("' . $column . '"))';
        $syntax[] = '    <span class="help-block">{{ $errors->first("' . $column . '") }}</span>';
        $syntax[] = '   @endif';
        $syntax[] = '</div>';

        return join("\n".str_repeat(' ', 20), $syntax);
    }


    /**
     * Construct the syntax to drop a column.
     *
     * @param  string $field
     * @return string
     */
    private function dropColumn($field)
    {
        return sprintf("\$table->dropColumn('%s');", $field['name']);
    }


    /**
     * Construct the controller fields
     *
     * @param $schema
     * @param $meta
     * @return string
     */
    private function createSchemaForControllerMethod($schema, $meta)
    {


        if (!$schema) return '';

        $fields = array_map(function ($field) use ($meta) {
            return $this->AddColumn($field, 'controller', $meta);
        }, $schema);


        return implode("\n" . str_repeat(' ', 8), $fields);
    }


    /**
     * Construct the view fields
     *
     * @param $schema
     * @param $meta
     * @param string $type Params 'header' or 'content'
     * @return string
     */
    private function createSchemaForViewMethod($schema, $meta, $type = 'index-header')
    {


        if (!$schema) return '';

        $fields = array_map(function ($field) use ($meta, $type) {
            return $this->AddColumn($field, 'view-' . $type, $meta);
        }, $schema);


        // Format code
        if ($type == 'index-header') {
            return implode("\n" . str_repeat(' ', 24), $fields);
        } else {
            // index-content
            return implode("\n" . str_repeat(' ', 20), $fields);
        }

    }

    private function htmlField($column, $variable, $field, $type)
    {

        $value = '{{ old("'.$column.'") }}';

        if($type == 'view-edit-content')
        {
            $value = '{{ is_null(old("'.$column.'")) ? $'.$variable.'->'.$column.' : old("'.$column.'") }}';
        }

        switch ($field['type']) {
            case 'date':
                $layout = "<input type=\"text\" id=\"$column-field\" name=\"$column\" class=\"form-control date-picker\" value=\"$value\"/>";
                break;
            case 'boolean':
                $layout = "<div class=\"btn-group\" data-toggle=\"buttons\"><label class=\"btn btn-primary\"><input type=\"radio\" value=\"true\" name=\"$column\" id=\"$column-field\" autocomplete=\"off\"> True</label><label class=\"btn btn-primary active\"><input type=\"radio\" name=\"$column-field\" value=\"false\" id=\"$column-field\" autocomplete=\"off\"> False</label></div>";
                break;
            case 'text':
                $layout = "<textarea class=\"form-control\" id=\"$column-field\" rows=\"3\" name=\"$column\">$value</textarea>";
                break;
            case 'string':
            default:
                $layout = "<input type=\"text\" id=\"$column-field\" name=\"$column\" class=\"form-control\" value=\"$value\"/>";
        }

        return $layout;
    }
}
