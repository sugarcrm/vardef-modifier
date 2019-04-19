<?php

namespace DRI\SugarCRM\VardefModifier;

use Symfony\Component\Yaml\Yaml;

/**
 * @author Emil Kilhage
 */
class YamlParser
{
    /**
     * @param string $file
     * @return array
     * @throws Exception\InvalidFilePath
     */
    public static function parse($file)
    {
        if (!file_exists($file)) {
            throw new Exception\InvalidFilePath($file);
        }

        $def = Yaml::parse($file);

        return $def;
    }
}
