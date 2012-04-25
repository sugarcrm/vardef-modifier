<?php

if (!class_exists('Spyc'))
{
    require_once __DIR__ . '/spyc.php';
}

require_once __DIR__ . '/VardefModifier/Exception.php';

/**
 * Simplifes modifications of SugarCrm vardef definitions
 *
 * @author Emil Kilhage
 */
class VardefModifier
{

    /**
     * Holds the default field definitions that all fields are built from
     * This is loaded from the ./defaults.yml file by VardefModifier::loadDefaults
     * @var array
     */
    private static $_defaults;

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
     * @return array
     * @throws VardefModifier_Exception
     */
    private static function loadDefaults()
    {
        if (!isset(self::$_defaults))
        {
            $file = __DIR__ . '/defaults.yml';
            self::$_defaults = spyc_load_file($file);
        }
    }

    /**
     * Recursive helper method used by VardefModifier::remove
     *
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
     * @param array $a1
     * @param array $a2
     * @return array
     */
    private static function merge(array $a1, array $a2)
    {
        foreach ($a2 as $key => $value)
        {
            if (is_array($value))
            {
                if (isset($a1[$key]) && is_array($a1[$key]))
                {
                    $a1[$key] = static::merge($a1[$key], $value);
                }
                else
                {
                    $a1[$key] = $value;
                }
            }
            else
            {
                $a1[$key] = $value;
            }
        }
        return $a1;
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
    private static function _getTableName($module_name)
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
     * @var string
     */
    private $table_name;

    /**
     * @param string $module_name
     * @param array $dictionary
     */
    public function __construct($module_name, array $dictionary)
    {
        self::loadDefaults();
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
     * Sets Defaults, Adds, Removes and Changes the vardef
     *
     * Uses pretty much the whole class based on a array
     *
     * Possible keys:
     *
     *   - defaults: see VardefModifier::defaults
     *   - add:      see VardefModifier::add
     *   - change:   see VardefModifier::change
     *   - remove:   see VardefModifier::remove
     *
     * @param array $def
     * @return \VardefModifier
     * @throws VardefModifier_Exception
     */
    public function def(array $def)
    {
        static $keys = array ('defaults', 'add', 'change', 'remove');
        // This methods needs to be executed to the correct order
        foreach ($keys as $key)
        {
            if (isset($def[$key]))
            {
                $this->$key($def[$key]);
                unset($def[$key]);
            }
        }
        if (!empty($def))
        {
            throw new VardefModifier_Exception(
                'Invalid key(s): ' . implode(', ', array_keys($def))
            );
        }
        return $this;
    }

    /**
     * Adds fields, indices and relationships to the vardef
     *
     * Possible keys:
     *
     *   - fields:        see VardefModifier::addFields
     *   - indices:       see VardefModifier::addIndices
     *   - relationships: see VardefModifier::addRelationships
     *
     * @param array $fields
     * @return VardefModifier
     */
    public function add(array $keys)
    {
        foreach ($keys as $key => $fields)
        {
            if (!is_array ($fields))
                throw new VardefModifier_Exception("Invalid Array Formatting");
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
     * Adds many indices to the vardef from a array definition
     *
     * @todo
     * @param array $indices
     * @return \VardefModifier
     */
    public function addIndices(array $indices)
    {
        foreach ($indices as $fields => $settings)
        {
            if (is_int($fields))
            {
                if (is_string($settings)
                    || (is_array ($settings) && isset($settings[0])))
                {
                    $fields = $settings;
                    $settings = array ();
                }
            }

            if (!is_array($settings))
            {
                throw new VardefModifier_Exception("Invalid type of settings");
            }

            $this->addIndex($fields, $settings);
        }
        return $this;
    }

    /**
     * Adds a index to the vardef
     *
     * @todo
     * @return \VardefModifier
     */
    public function addIndex($fields, array $settings = array ())
    {
        $fields = (array) $fields;
        $name = 'idx_' . $this->getTableName() . '_' . implode('_', $fields);
        $default = array ('name' => $name, 'fields' => $fields);
        $index = array_merge($this->getDefault('index'), $default, $settings);
        $this->vardef['indices'][$index['name']] = $index;
        return $this;
    }

    /**
     * Adds relationships to the vardef from a array definition
     *
     * @param array $relationships
     * @return \VardefModifier
     */
    public function addRelationships(array $relationships)
    {
        foreach ($relationships as $name => $settings)
        {
            if (is_int($name))
            {
                $name = $settings;
                $settings = array ();
            }
            $this->addRelationship($name, $settings);
        }
        return $this;
    }

    /**
     * Adds a relationship to the vardef
     *
     * @param string $name: name of the relation or the module name
     * @param string|array $settings: module name or relationship settings
     * @return \VardefModifier
     */
    public function addRelationship($name, $settings = array ())
    {
        $relationship_names = array ($this->object_name);
        if (is_string($settings))
        {
            $settings = array ('module' => $settings);
            $relationship_names[] = $name;
        }
        elseif (empty($settings['module']))
        {
            $settings['module'] = $name;
            $name = strtolower(self::getObjectName($settings['module']));
        }
        else
        {
            $relationship_names[] = $name;
        }

        $relationship_names[] = $settings['module'];
        $relationship_name = strtolower(implode('_', $relationship_names));

        $vname = isset($settings['vname']) ? $settings['vname'] : $this->getVName($name);
        $rhs_key = $name . '_id';

        $_settings = static::merge($this->getDefault('relationship'), array (
            'id' => array (
                'name' => $rhs_key,
                'vname' => $vname
            ),
            'name' => array (
                'name' => $name . '_name',
                'vname' => $vname,
                'module' => $settings['module'],
            ),
            'link' => array (
                'name' => $name . '_link',
                'vname' => $vname,
                'module' => $settings['module']
            ),
            'index' => array (),
            'relationship' => array (
                'lhs_module' => $settings['module'],
                'lhs_table' => self::_getTableName($settings['module']),
                'rhs_module' => $this->module_name,
                'rhs_table' => $this->getTableName(),
                'rhs_key' => $rhs_key,
                'name' => $relationship_name
            ),
        ));

        // Set the name field to required if set in the root
        if (isset($settings['required']))
        {
            $_settings['name']['required'] = $settings['required'];
        }

        $_settings = static::merge($_settings, $settings);

        // Make sure that the id field name are synced
        $_settings['name']['id_name'] = $_settings['id']['name'];
        $_settings['relationship']['rhs_key'] = $_settings['id']['name'];

        // Make sure that the relationship names are synced
        $relationship_name = $_settings['relationship']['name'];
        $_settings['link']['relationship'] = $relationship_name;
        unset($_settings['relationship']['name']);

        // Add the built releationship
        $this->vardef['relationships'][$relationship_name] = $_settings['relationship'];

        // Add the fields
        foreach (array ('id', 'name', 'link') as $type)
        {
            $this->addField($_settings[$type]['name'], $type, $_settings[$type]);
        }

        // Add the index if not set to non-array value
        if (isset($_settings['index']) && is_array($_settings['index']))
        {
            $this->addIndex($_settings['id']['name'], $_settings['index']);
        }

        return $this;
    }

    /**
     * Makes changes to the vardefs
     *
     * @param array $change
     * @return \VardefModifier
     */
    public function change(array $changes)
    {
        $this->vardef = array_merge_recursive($this->vardef, $changes);
        return $this;
    }

    /**
     * Removes fields / properties this the vardef
     *
     * @param array $keys
     */
    public function remove(array $values)
    {
        static::_remove($values, $this->vardef);
        return $this;
    }

    /**
     * Changes the default field properties
     *
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
     * Adds fields based on a array definition
     *
     * The keys in the array should be the field type
     * and the value an array of fields and definitions
     *
     * See VardefModifier::addField for supported field types
     *
     * @param array $fields
     * @return \VardefModifier
     */
    public function addFields(array $types)
    {
        foreach ($types as $type => $fields)
        {
            if (!is_array ($fields))
                throw new VardefModifier_Exception("Invalid Array Formatting");
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
        if (!is_string($name) || empty($name))
        {
            throw new VardefModifier_Exception("Invalid type of name");
        }
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
            case 'name':
                $this->addName($name, $settings);
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
            $this->addRelationship('Currencies', array (
                'id' => array (
                    'group' => 'currency_id',
                    'function' => array (
                        'name' => 'getCurrencyDropDown',
                        'returns' => 'html'
                    ),
                ),
                'name' => array (
                    'function' => array (
                        'name' => 'getCurrencyNameDropDown',
                        'returns' => 'html',
                    )
                )
            ));
            $this->addRelate('currency_symbol', array (
                'module' => 'Currencies',
                'rname' => 'symbol',
                'function' => array (
                    'name' => 'getCurrencySymbolDropDown',
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
     * @return string
     */
    private function getTableName()
    {
        if (!isset($this->table_name))
            $this->table_name = self::_getTableName($this->module_name);
        return $this->table_name;
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
     * @param array $field_default
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
    private function addName($name, array $settings)
    {
        return $this->addRelate($name, array_merge(
            $this->getDefault('name'),
            $settings
        ));
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
            'table' => self::_getTableName($settings['module']),
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
     * Adds a link field
     *
     * You have to specify module and relationship in the settings array
     *
     * @param string $name
     * @param array $settings
     * @return \VardefModifier
     */
    private function addLink($name, array $settings = array ())
    {
        if (empty($settings['module']))
        {
            $settings['module'] = $name;
            $name = strtolower($name);
        }

        $object_name = self::getObjectName($settings['module']);

        if (empty($settings['relationship']))
        {
            $settings['relationship'] = strtolower(
                $object_name . '_' . $this->module_name
            );
        }

        $this->addFieldToVardef($name, array_merge(
            $this->getDefault('link'),
            array ('bean_name' => $object_name),
            $settings
        ));

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
        $args[0] = $this->getBase();
        $this->addFieldToVardef($name, call_user_func_array(
            'array_merge', $args
        ));
        return $this;
    }

    /**
     * @return array
     */
    private function getBase()
    {
        return $this->getDefault('_base');
    }

    /**
     * @param string $name
     * @return array
     */
    private function addFieldToVardef($name, array $definition)
    {
        $this->vardef['fields'][$name] = array_merge(array (
            'name' => $name,
            'vname' => $this->getVName($name),
        ), $definition);
    }

    private function getVName($name)
    {
        return 'LBL_' . strtoupper($name);
    }

}
