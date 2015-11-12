<?php

namespace DRI\SugarCRM\VardefModifier;

use DRI\SugarCRM\VardefModifier\Exception\UnsupportedVersionException;
use DRI\SugarCRM\VardefModifier\Version;
use Symfony\Component\Yaml\Yaml;

/**
 * @author Emil Kilhage
 */
class VardefModifierFactory
{
    /**
     * @var string
     */
    private $moduleName;

    /**
     * @var array
     */
    private $dictionary;

    /**
     * VardefModifierFactory constructor.
     *
     * @param string $moduleName
     * @param array  $dictionary
     */
    public function __construct($moduleName, array $dictionary = array ())
    {
        $this->moduleName = $moduleName;
        $this->dictionary = $dictionary;
    }

    /**
     * Loads a yaml file and parses its contents
     *
     * @param string $file
     * @return VardefModifier
     */
    public function yaml($file)
    {
        $def = YamlParser::parse($file);
        $vardefModifier = $this->factoryFromDefinition($def);
        $vardefModifier->def($def);
        return $vardefModifier;
    }

    /**
     * Factories a VardefModifier instance based on a definition
     *
     * @param array $def
     * @return VardefModifier
     * @throws UnsupportedVersionException
     */
    private function factoryFromDefinition(array $def)
    {
        if (!array_key_exists('version', $def) || (int)$def['version'] === 1) {
            return new VardefModifier($this->moduleName, $this->dictionary);
        } elseif ((int)$def['version'] === 2) {
            return new VardefModifier\Version2x($this->moduleName, $this->dictionary);
        } else {
            throw new UnsupportedVersionException();
        }
    }
}
