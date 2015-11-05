<?php

namespace DRI\SugarCRM\VardefModifier\Exception;

use DRI\SugarCRM\VardefModifier\Exception;

/**
 * @author Emil Kilhage
 */
class InvalidDefinitionFormat extends Exception
{
    public function __construct($message = '')
    {
        parent::__construct("Invalid Definition Formatting: '$message'");
    }
}
