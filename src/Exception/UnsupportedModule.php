<?php

namespace DRI\SugarCRM\VardefModifier\Exception;

use DRI\SugarCRM\VardefModifier\Exception;

/**
 * @author Emil Kilhage
 */
class UnsupportedModule extends Exception
{
    /**
     * UnsupportedModule constructor.
     * @param string $module_name
     */
    public function __construct($module_name)
    {
        parent::__construct("Unsupported module name $module_name");
    }
}
