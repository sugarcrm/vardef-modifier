<?php

namespace DRI\SugarCRM\VardefModifier\Exception;

use DRI\SugarCRM\VardefModifier\Exception;

class RecursiveException extends Exception
{
    /**
     * @var string
     */
    public $table_name;

    /**
     * RecursiveException constructor.
     * @param string $table_name
     */
    public function __construct($table_name)
    {
        $this->table_name = $table_name;
        parent::__construct();
    }
}
