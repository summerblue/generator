<?php

namespace Summerblue\Generator\Localizations;

/**
 * Class SyntaxBuilder
 * @package Summerblue\Generator\Migrations
 * @author Ryan Gurnick <ryangurnick@gmail.com>
 */
class SyntaxBuilder
{

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
    public function create($schema)
    {
        $fieldsc = $this->createSchemaForLocalization($schema);
        return $fieldsc;
    }

    private function createSchemaForLocalization($schema)
    {
        $localization = '';
        if(is_array($schema)) {
            foreach ($schema as $k => $v) {
                $localization .= "'" . $v['name'] . "' => '" . $v['argument'] . "',\n\t";
            }
        }
        return $localization;
    }
}