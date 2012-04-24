<?php

if (!class_exists('Spyc'))
{
    require_once __DIR__ . '/spyc.php';
}

require_once __DIR__ . '/VardefModifier/Exception.php';

/**
 * @author Emil Kilhage
 */
class VardefModifier
{

    /**
     * @var array
     */
    private static $_defaults = array (
        'varchar' => array ('type' => 'varchar', 'len' => '255'),
        'int' => array ('type' => 'int', 'len' => '8'),
        'text' => array ('type' => 'text'),
        'date' => array ('type' => 'date'),
        'decimal' => array ('type' => 'decimal', 'len' => '26,6'),
        'image' => array ('type' => 'image', 'dbType' => "varchar", 'height' => '100'),
        'datetimecombo' => array ('type' => 'datetimecombo', 'dbType' => 'datetime'),
        'url' => array ('type' => 'url', 'dbType' => 'varchar'),
        'datetime' => array ('type' => 'datetime'),
        'bool' => array ('type' => 'bool'),
        'float' => array ('type' => 'float'),
        'phone' => array ('type' => 'phone', 'dbType' => 'varchar', 'len' => 100),
        'id' => array ('type' => 'id'),
        'currency' => array ('type' => 'currency', 'len' => '26,6', 'dbType' => 'decimal'),
        'enum' => array ('type' => 'enum'),
        'multienum' => array ('type' => 'multienum', 'isMultiSelect' => true),
        'relate' => array ('source' => 'non-db', 'type' => 'relate'),
        '_base' => array ('required' => false, 'reportable' => true, 'audited' => true, 'importable' => 'true', 'massupdate' => false),
    );

    /**
     * @param string $module_name
     * @param array $dictionary
     * @return VardefModifier
     */
    public static function modify($module_name, array $dictionary)
    {
        return new VardefModifier($module_name, $dictionary);
    }

    /**
     * @param array $values
     * @param array $from
     */
    private static function _remove(array $values, array & $from)
    {
        foreach ($values as $key => $value)
        {
            if (is_array($value))
            {
                if (isset($from[$key]))
                {
                    self::_remove($value, $from[$key]);
                }
            }
            else
            {
                if (isset($from[$value]))
                {
                    unset($from[$value]);
                }
            }
        }
    }

    /**
     * @global array $beanList
     * @param string $module_name
     * @return string
     * @throws VardefModifier_Exception
     */
    private static function getObjectName($module_name)
    {
        global $beanList;
        if (!isset($beanList[$module_name]))
        {
            throw new VardefModifier_Exception("Invalid Module Name: $module_name");
        }
        return $beanList[$module_name];
    }

    /**
     * @param string $module_name
     * @return string
     */
    private static function getTableName($module_name)
    {
        return strtolower($module_name);
    }

    /**
     * @var array
     */
    private $dictionary;

    /**
     * @var array
     */
    private $vardef;

    /**
     * @var string
     */
    private $module_name;

    /**
     * @var string
     */
    private $object_name;

    /**
     * @param string $module_name
     * @param array $dictionary
     */
    public function __construct($module_name, array $dictionary)
    {
        $this->module_name = $module_name;
        $this->object_name = self::getObjectName($this->module_name);
        $this->dictionary = $dictionary;

        if (!isset($this->dictionary[$this->object_name]))
        {
            $this->dictionary[$this->object_name] = array ();
        }

        $this->vardef = $this->dictionary[$this->object_name];
        $this->defaults = self::$_defaults;
    }

    /**
     * @param string $file
     * @return \VardefModifier
     * @throws VardefModifier_Exception
     */
    public function yaml($file)
    {
        if (!file_exists($file))
        {
            throw new VardefModifier_Exception("Can't find file: $file");
        }
        return $this->def(Spyc::YAMLLoad($file));
    }

    /**
     * @param array $def
     * @return \VardefModifier
     * @throws VardefModifier_Exception
     */
    public function def(array $def)
    {
        // Since add, remove and change may depend on
        // these default settings, we'll need to do this first
        if (isset($def['defaults']))
        {
            $this->defaults($def['defaults']);
        }
        foreach ($def as $key => $fields)
        {
            switch ($key)
            {
                case 'add':
                    $this->add($fields);
                    break;
                case 'remove':
                    $this->remove($fields);
                    break;
                case 'change':
                    $this->change($fields);
                    break;
                default:
                    if ($key !== 'defaults')
                    {
                        throw new VardefModifier_Exception("Invalid key: $key");
                    }
            }
        }
        return $this;
    }

    /**
     * @param array $fields
     * @return VardefModifier
     */
    public function add(array $keys)
    {
        foreach ($keys as $key => $fields)
        {
            switch ($key)
            {
                case 'fields':
                    $this->addFields($fields);
                    break;
                case 'indices':
                    $this->addIndices($fields);
                    break;
                case 'relationships':
                    $this->addRelationships($fields);
                    break;
                default:
                    throw new VardefModifier_Exception("Invalid key: $key");
            }
        }
        return $this;
    }

    /**
     * @todo
     * @param array $indices
     * @return \VardefModifier
     */
    public function addIndices(array $indices)
    {
        throw new VardefModifier_Exception(__METHOD__ . ' Not Implemented');
        return $this;
    }

    /**
     * @todo
     * @return \VardefModifier
     */
    public function addIndex($fields, $settings)
    {
        throw new VardefModifier_Exception(__METHOD__ . ' Not Implemented');
        return $this;
    }

    /**
     * @todo
     * @return \VardefModifier
     */
    public function addRelationships()
    {
        throw new VardefModifier_Exception(__METHOD__ . ' Not Implemented');
        return $this;
    }

    /**
     * @todo
     * @return \VardefModifier
     */
    public function addRelationship()
    {
        throw new VardefModifier_Exception(__METHOD__ . ' Not Implemented');
        return $this;
    }

    /**
     * @param array $change
     * @return \VardefModifier
     */
    public function change(array $changes)
    {
        $this->vardef = array_merge_recursive($this->vardef, $changes);
        return $this;
    }

    /**
     * @param array $keys
     */
    public function remove(array $values)
    {
        static::_remove($values, $this->vardef);
        return $this;
    }

    /**
     * @param array $field_defaults
     * @throws VardefModifier_Exception
     */
    public function defaults(array $field_defaults)
    {
        foreach ($field_defaults as $name => $field_default)
        {
            $this->setDefault($name, $field_default);
        }
        return $this;
    }

    /**
     * @param array $fields
     * @return \VardefModifier
     */
    public function addFields(array $fields)
    {
        foreach ($fields as $type => $fields)
        {
            foreach ($fields as $name => $settings)
            {
                if (is_int($name))
                {
                    $name = $settings;
                    $this->addField($name, $type);
                }
                else
                {
                    $this->addField($name, $type, $settings);
                }
            }
        }
        return $this;
    }

    /**
     * Supported field types:
     *
     *    - enum
     *    - multienum
     *    - link
     *    - currency
     *    - relate
     *    - varchar
     *    - int
     *    - text
     *    - date
     *    - decimal
     *    - image
     *    - datetimecombo
     *    - url
     *    - datetime
     *    - bool
     *    - float
     *    - phone
     *    - id
     *
     * @param string $name
     * @param string $type
     * @param array $settings
     */
    public function addField($name, $type, array $settings = array ())
    {
        switch ($type)
        {
            case 'enum':
                $this->addEnum($name, $settings);
                break;
            case 'multienum':
                $this->addMultienum($name, $settings);
                break;
            case 'link':
                $this->addLink($name, $settings);
                break;
            case 'currency':
                $this->addCurrency($name, $settings);
                break;
            case 'relate':
                $this->addRelate($name, $settings);
                break;
            default:
                if ($this->hasDefault($type))
                {
                    $this->addDefaultField($name, $this->getDefault($type), $settings);
                }
                else
                {
                    throw new VardefModifier_Exception("Invalid Type: $type");
                }
        }
        return $this;
    }

    /**
     * @param string $name
     * @return boolean
     */
    public function hasField($name)
    {
        return isset($this->vardef['fields'][$name]);
    }

    /**
     * @return \VardefModifier
     */
    public function addCurrencyRelation()
    {
        if (!$this->hasField('currency_id'))
        {
            $this->addField('currency_id', 'id', array (
                'group' => 'currency_id',
                'function' => array (
                    'name' => 'getCurrencyDropDown',
                    'returns' => 'html'
                ),
            ));
        }

        if (!$this->hasField('currency_symbol'))
        {
            $this->addRelate('currency_symbol', array (
                'module' => 'Currencies',
                'rname' => 'symbol',
                'function' => array (
                    'name' => 'getCurrencySymbolDropDown',
                    'returns' => 'html',
                )
            ));
        }

        if (!$this->hasField('currency_name'))
        {
            $this->addRelate('currency_name', array (
                'module' => 'Currencies',
                'rname' => 'name',
                'function' => array (
                    'name' => 'getCurrencyNameDropDown',
                    'returns' => 'html',
                )
            ));
        }
        return $this;
    }

    /**
     * @return array
     */
    public function get()
    {
        $this->dictionary[$this->object_name] = $this->vardef;
        return $this->dictionary;
    }

    /**
     * @param string $type
     * @return array
     */
    private function getDefault($type)
    {
        if (!isset($this->defaults[$type]))
        {
            throw new VardefModifier_Exception("Invalid default type: $type");
        }
        return $this->defaults[$type];
    }

    /**
     * @param string $type
     * @return boolean
     */
    private function hasDefault($type)
    {
        return isset($this->defaults[$type]);
    }

    /**
     * @param string $name
     * @param array @field_default
     * @throws VardefModifier_Exception
     */
    private function setDefault($name, array $field_default)
    {
        if (!isset($this->defaults[$name]))
        {
            throw new VardefModifier_Exception("Invalid Field Default: $name");
        }
        $this->defaults[$name] = array_merge($this->defaults[$name], $field_default);
    }

    /**
     * @param string $name
     * @param array $settings
     * @return \VardefModifier
     */
    private function addRelate($name, array $settings)
    {
        if (!isset($settings['module']))
        {
            throw new VardefModifier_Exception("Missing module");
        }
        $default = array (
            'rname' => $name,
            'table' => self::getTableName($settings['module']),
            'id_name' => strtolower(self::getObjectName($settings['module'])) . '_id',
        );
        return $this->addDefaultField(
            $name,
            $this->getDefault('relate'),
            $default,
            $settings
        );
    }

    /**
     * @param string $name
     * @param array
     * @return \VardefModifier
     */
    private function addCurrency($name, array $settings = array ())
    {
        $template = $this->getDefault('currency');
        return $this->
            addCurrencyRelation()->
            addDefaultField(
                $name,
                $template,
                $settings
            )->
            addDefaultField(
                $name . '_usdollar',
                $template,
                array ('group' => $name),
                $settings
            );
    }

    /**
     * @todo
     * @param string $name
     * @param array $settings
     * @return \VardefModifier
     */
    private function addLink($name, array $settings = array ())
    {
        throw new VardefModifier_Exception(__METHOD__ . ' Not Implemented');
        return $this;
    }

    /**
     * @param string $name
     * @param array $settings
     * @return \VardefModifier
     */
    private function addEnum($name, array $settings = array ())
    {
        return $this->addEnumLike($name, $this->getDefault('enum'), $settings);
    }

    /**
     * @param string $name
     * @param array $settings
     * @return \VardefModifier
     */
    private function addMultienum($name, array $settings = array ())
    {
        return $this->addEnumLike($name, $this->getDefault('multienum'), $settings);
    }

    /**
     * @param string $name
     * @param array $settings
     * @param array $default
     * @return \VardefModifier
     */
    private function addEnumLike($name, array $default, array $settings)
    {
        return $this->addDefaultField(
            $name,
            array ('options' => strtolower($this->module_name . '_' . $name) . '_list'),
            $default,
            $settings
        );
    }

    /**
     * @param string $name
     * @return \VardefModifier
     */
    private function addDefaultField($name)
    {
        $args = func_get_args();
        $args[0] = $this->createDefault($name);
        $this->vardef['fields'][$name] = call_user_func_array(
            'array_merge', $args
        );
        return $this;
    }

    /**
     * @param string $name
     * @return array
     */
    private function createDefault($name)
    {
        return array_merge($this->getDefault('_base'), array (
            'name' => $name,
            'vname' => 'LBL_' . strtoupper($name),
        ));
    }

}
