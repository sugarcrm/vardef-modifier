<?php

namespace DRI\SugarCRM\VardefModifier\Exception;

use DRI\SugarCRM\VardefModifier\Exception;

/**
 * @author Emil Kilhage
 */
class UnableToWriteCacheFile extends Exception
{
    /**
     * UnableToWriteCacheFile constructor.
     * @param string $file
     */
    public function __construct($file)
    {
        parent::__construct("Unable to write cache file: $file");
    }
}
