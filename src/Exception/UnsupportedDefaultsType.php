<?php

namespace DRI\SugarCRM\VardefModifier\Exception;

use DRI\SugarCRM\VardefModifier\Exception;

/**
 * @author Emil Kilhage
 */
class UnsupportedDefaultsType extends Exception
{
    /**
     * UnsupportedDefaultsType constructor.
     * @param string $type
     */
    public function __construct($type)
    {
        parent::__construct("Invalid default type: $type");
    }
}
