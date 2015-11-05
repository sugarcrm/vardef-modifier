<?php

namespace DRI\SugarCRM\VardefModifier\Exception;

use DRI\SugarCRM\VardefModifier\Exception;

/**
 * @author Emil Kilhage
 */
class MissingTableName extends Exception
{
    public function __construct($module_name)
    {
        parent::__construct("Missing table name for module $module_name");
    }
}
