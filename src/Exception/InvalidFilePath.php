<?php

namespace DRI\SugarCRM\VardefModifier\Exception;

use DRI\SugarCRM\VardefModifier\Exception;

/**
 * @author Emil Kilhage
 */
class InvalidFilePath extends Exception
{
    public function __construct($file)
    {
        parent::__construct("Can't find file: $file");
    }
}
