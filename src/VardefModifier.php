<?php

namespace DRI\SugarCRM\VardefModifier;

/**
 * Simplifies modifications of SugarCRM vardef definitions.
 *
 * @author Emil Kilhage
 */
class VardefModifier
{
    const VERSION = 2;

    /**
     * @var int
     */
    private $version = 1;

    /**
     * Holds the default field definitions that all fields are built from
     * This is loaded from the ./defaults.yml file by VardefModifier::loadDefaults.
     *
     * @var array
     */
    private static $_defaults;

    /**
     * Modules that doesn't have the object name as dictionary key are listed here.
     *
     * @var array
     */
    private static $special_dictionary_key_mappings = array(
        'Cases' => 'Case',
    );

    /**
     * @param string $module_name
     * @param array  $dictionary
     *
     * @return VardefModifierFactory
     * @throws Exception
     */
    public static function modify($module_name, array $dictionary = array ())
    {
        return new VardefModifierFactory($module_name, $dictionary);
    }

    /**
     * @return array
     *
     * @throws Exception
     */
    private static function loadDefaults()
    {
        if (!isset(self::$_defaults)) {
            $file = dirname(__DIR__).'/defaults.yml';
            self::$_defaults = YamlParser::parse($file);
        }
    }

    /**
     * Recursive helper method used by VardefModifier::remove.
     *
     * @param array $values
     * @param array $from
     */
    private static function _remove(array $values, array &$from)
    {
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                if (isset($from[$key])) {
                    self::_remove($value, $from[$key]);
                }
            } else {
                if (isset($from[$value])) {
                    unset($from[$value]);
                }
            }
        }
    }

    /**
     * @param array $a1
     * @param array $a2
     *
     * @return array
     */
    private static function merge(array $a1, array $a2)
    {
        foreach ($a2 as $key => $value) {
            if (is_array($value)) {
                if (isset($a1[$key]) && is_array($a1[$key])) {
                    $a1[$key] = static::merge($a1[$key], $value);
                } else {
                    $a1[$key] = $value;
                }
            } else {
                $a1[$key] = $value;
            }
        }

        return $a1;
    }

    /**
     * @global array $beanList
     *
     * @param string $module_name
     *
     * @return string
     *
     * @throws Exception
     */
    private static function getObjectName($module_name)
    {
        global $beanList;

        if (!isset($beanList[$module_name])) {
            throw new Exception\UnsupportedModule("$module_name");
        }

        return $beanList[$module_name];
    }

    /**
     * @param string $module_name
     *
     * @param array $dictionary
     * @return string
     * @throws Exception
     */
    private static function _getTableName($module_name, array $dictionary)
    {
        $object_name = self::getObjectName($module_name);

        if (!empty($dictionary[$object_name]['table'])) {
            return $dictionary[$object_name]['table'];
        } else {
            if (!empty($GLOBALS['dictionary'][$object_name]['table'])) {
                return $GLOBALS['dictionary'][$object_name]['table'];
            } else {
                global $beanFiles;
                $bean_name = self::getObjectName($module_name);

                if (isset($beanFiles[$bean_name])) {
                    require_once $beanFiles[$bean_name];

                    if (!class_exists($bean_name)) {
                        throw new Exception\UnsupportedModule($module_name);
                    }

                    $refl = new \ReflectionClass($bean_name);
                    $props = $refl->getDefaultProperties();

                    if (!empty($props['table_name'])) {
                        return $props['table_name'];
                    } else {
                        throw new Exception\UnsupportedModule($module_name);
                    }
                } else {
                    throw new Exception\UnsupportedModule($module_name);
                }
            }
        }
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
     * @param array  $dictionary
     * @throws Exception
     */
    public function __construct($module_name, array $dictionary)
    {
        self::loadDefaults();

        $this->version = new Version();
        $this->module_name = $module_name;
        $this->object_name = self::getObjectName($this->module_name);
        $this->dictionary = $dictionary;

        $dictionary_key = $this->getDictionaryKey();

        if (!isset($this->dictionary[$dictionary_key])) {
            $this->dictionary[$dictionary_key] = array();
        }

        $this->vardef = $this->dictionary[$dictionary_key];
        $this->defaults = self::$_defaults;
    }

    /**
     * @return string: the name of the dictionary key for the current module
     */
    public function getDictionaryKey()
    {
        if (isset(self::$special_dictionary_key_mappings[$this->module_name])) {
            return self::$special_dictionary_key_mappings[$this->module_name];
        }

        return $this->object_name;
    }

    /**
     * @param string $file
     *
     * @return VardefModifier
     *
     * @throws Exception
     * @throws Exception\InvalidFilePath
     */
    public function yaml($file)
    {
        $def = YamlParser::parse($file);
        return $this->def($def);
    }

    /**
     * Sets Defaults, Adds, Removes and Changes the vardef.
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
     *
     * @return VardefModifier
     *
     * @throws Exception
     */
    public function def(array $def)
    {
        static $keys = array(
            'version',
            'defaults',
            'add',
            'change',
            'remove',
        );

        try {
            // These methods needs to be executed to the correct order
            foreach ($keys as $key) {
                if (array_key_exists($key, $def)) {
                    if (is_array($def[$key])) {
                        $this->$key($def[$key]);
                    }

                    unset($def[$key]);
                }
            }

            if (count($def) > 0) {
                throw new Exception\InvalidDefinitionFormat(
                    'Invalid key(s): '.implode(', ', array_keys($def))
                );
            }
        } catch (Exception $e) {
            echo "$e\n";
        }

        return $this;
    }

    /**
     * Adds fields, indices and relationships to the vardef.
     *
     * Possible keys:
     *
     *   - fields:        see VardefModifier::addFields
     *   - indices:       see VardefModifier::addIndices
     *   - relationships: see VardefModifier::addRelationships
     *
     * @param array $keys
     * @return VardefModifier
     * @throws Exception
     */
    public function add(array $keys)
    {
        foreach ($keys as $key => $fields) {
            if (!is_array($fields)) {
                continue;
            }

            switch ($key) {
                case 'version':
                    $this->setVersion($fields);
                    break;
                case 'fields':
                    $this->addFields($fields);
                    break;
                case 'indices':
                    $this->addIndices($fields);
                    break;
                case 'relationships':
                    $this->addRelationships($fields);
                    break;
                case 'duplicate_check':
                    $this->addDuplicateCheck($fields);
                    break;
                case 'acls':
                    $this->addAcls($fields);
                    break;
                case 'visibility':
                    $this->addVisibility($fields);
                    break;
                default:
                    throw new Exception\InvalidDefinitionFormat("$key is not supported, only fields, indices relationships, duplicate_check, acls");
            }
        }

        return $this;
    }

    /**
     * @param int $version
     */
    private function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * Adds many indices to the vardef from a array definition.
     *
     * @param array $indices
     *
     * @return VardefModifier
     * @throws Exception
     */
    public function addIndices(array $indices)
    {
        foreach ($indices as $fields => $settings) {
            if (is_int($fields) &&
                (is_string($settings) ||
                (is_array($settings) && isset($settings[0])))) {
                $fields = $settings;
                $settings = array();
            }

            $this->addIndex($fields, $settings);
        }

        return $this;
    }

    /**
     * Adds a index to the vardef.
     *
     * @param array|string $fields
     * @param array|string $settings
     * @return VardefModifier
     * @throws Exception
     */
    public function addIndex($fields, $settings = array())
    {
        if (is_string($settings) && is_string($fields)) {
            $settings = array('type' => $settings);
        }

        if (!is_array($settings)) {
            throw new Exception\InvalidDefinitionFormat("\$settings must be array");
        }

        $fields = (array) $fields;
        $default = array('fields' => $fields);
        $index = array_merge($this->getDefault('index'), $default, $settings);
        $index = array_merge(array('name' => 'idx_'.implode('_', $index['fields'])), $index);
        $this->vardef['indices'][$index['name']] = $index;

        // Provides support in the import module to do a duplicate check on the unique fields.
        if ($index['type'] === 'unique') {
            $this->addIndex($fields, array(
                'type' => 'index',
                'source' => 'non-db',
                'name' => $index['name'].'_dup_check',
            ));
        }

        return $this;
    }

    /**
     * Adds relationships to the vardef from a array definition.
     *
     * @param array $relationships
     *
     * @return VardefModifier
     * @throws Exception
     */
    public function addRelationships(array $relationships)
    {
        foreach ($relationships as $name => $settings) {
            if (is_int($name)) {
                $name = $settings;
                $settings = array();
            }
            $this->addRelationship($name, $settings);
        }

        return $this;
    }

    /**
     * @param array $settings
     *
     * @return VardefModifier
     * @throws Exception
     */
    private function addActivityRelationship(array $settings)
    {
        $defaults = self::merge(
            $this->getDefault('Activities'), $settings
        );

        foreach ($defaults as $module => $settings) {
            $this->addFlexRelateLink($module, $settings);
        }

        return $this;
    }

    /**
     * @param string  $name
     * @param array   $settings
     *
     * @return VardefModifier
     * @throws Exception
     */
    public function addFlexRelateLink($name, $settings = array())
    {
        if (empty($settings['module'])) {
            $settings['module'] = $name;
            $name = strtolower($name);
        }

        $settings = self::merge(
            $this->getDefault('flex_relate_link'), $settings
        );

        $module = $settings['module'];

        $table_name = self::_getTableName($module, $this->dictionary);
        $relationship_name = $this->getTableName().'_flex_relate_'.$table_name;

        $id_name = $settings['prefix'].'_id';
        $type_name = $settings['prefix'].'_type';

        $settings = self::merge($settings, array(
            'link' => array(
                'relationship' => $relationship_name,
                'name' => $name,
                'module' => $module,
            ),
            'relationship' => array(
                'lhs_module' => $this->module_name,
                'lhs_table' => $this->getTableName(),
                'rhs_key' => $id_name,
                'rhs_module' => $module,
                'rhs_table' => $table_name,
                'relationship_role_column_value' => $this->module_name,
                'relationship_role_column' => $type_name,
            ),
        ));

        $this->addLink($name, $settings['link']);
        $this->vardef['relationships'][$relationship_name] = $settings['relationship'];

        return $this;
    }

    /**
     * @param string $prefix
     * @param array  $settings
     *
     * @return VardefModifier
     * @throws Exception
     */
    private function addFlexRelate($prefix, array $settings = array())
    {
        if (isset($settings['options'])) {
            $settings['name']['options'] = $settings['options'];
            $settings['name']['parent_type'] = $settings['options'];
            $settings['type']['parent_type'] = $settings['options'];
            $settings['type']['options'] = $settings['options'];
            unset($settings['options']);
        }

        if (isset($settings['required'])) {
            $settings['id']['required'] = $settings['required'];
            $settings['name']['required'] = $settings['required'];
            $settings['type']['required'] = $settings['required'];
            unset($settings['required']);
        }

        if (isset($settings['readonly'])) {
            $settings['id']['readonly'] = $settings['readonly'];
            $settings['name']['readonly'] = $settings['readonly'];
            $settings['type']['readonly'] = $settings['readonly'];
            unset($settings['readonly']);
        }

        if (isset($settings['help'])) {
            $settings['id']['help'] = $settings['help'];
            $settings['name']['help'] = $settings['help'];
            $settings['type']['help'] = $settings['help'];
            unset($settings['help']);
        }

        $id_name = $prefix.'_id';
        $name_name = $prefix.'_name';
        $type_name = $prefix.'_type';

        $settings['name']['id_name'] = $id_name;
        $settings['name']['type_name'] = $type_name;

        $settings = self::merge(
            $this->getDefault('flex_relate'), $settings
        );

        $this->addField($id_name, 'id', $settings['id']);
        $this->addField($name_name, 'varchar', $settings['name']);
        $this->addField($type_name, 'varchar', $settings['type']);
        $this->addIndex($id_name);

        return $this;
    }

    /**
     * Adds a relationship to the vardef.
     *
     * @param string       $name      name of the relation or the module name
     * @param array|string $settings  module name or relationship settings
     *
     * @return VardefModifier
     * @throws Exception
     */
    public function addRelationship($name, $settings = array())
    {
        switch ($name) {
            case 'Activities':
                return $this->addActivityRelationship($settings);
        }

        $relationship_names = array($this->object_name);
        if (is_string($settings)) {
            $settings = array('module' => $settings);
            $relationship_names[] = $name;
        } elseif (empty($settings['module'])) {
            $settings['module'] = $name;
            $name = strtolower(self::getObjectName($settings['module']));
        } else {
            $relationship_names[] = $name;
        }

        switch ($settings['module']) {
            case 'Contacts':
                $settings = self::merge(array(
                    'name' => array(
                        'rname' => 'name',
                        'db_concat_fields' => array('first_name', 'last_name'),
                    ),
                ), $settings);
        }

        $relationship_names[] = $settings['module'];
        $relationship_name = strtolower(implode('_', $relationship_names));

        $vname = isset($settings['vname']) ? $settings['vname'] : $this->getVName($name);
        $rhs_key = $name.'_id';

        $_settings = static::merge($this->getDefault('relationship'), array(
            'id' => array(
                'name' => $rhs_key,
                'vname' => $vname,
            ),
            'name' => array(
                'name' => $name.'_name',
                'vname' => $vname,
                'module' => $settings['module'],
            ),
            'link' => array(
                'name' => $name.'_link',
                'vname' => $vname,
                'module' => $settings['module'],
            ),
            'index' => array(),
            'relationship' => array(
                'lhs_module' => $settings['module'],
                'lhs_table' => self::_getTableName($settings['module'], $this->dictionary),
                'rhs_module' => $this->module_name,
                'rhs_table' => $this->getTableName(),
                'rhs_key' => $rhs_key,
                'name' => $relationship_name,
            ),
        ));

        // Set the name field to required if set in the root
        if (isset($settings['required'])) {
            $_settings['name']['required'] = $settings['required'];
        }

        if (isset($settings['readonly'])) {
            $_settings['name']['readonly'] = $settings['readonly'];
        }

        $_settings = static::merge($_settings, $settings);

        // Make sure that the id field name are synced
        $_settings['name']['id_name'] = $_settings['id']['name'];
        $_settings['relationship']['rhs_key'] = $_settings['id']['name'];

        // Make sure that the link field names are synced
        $_settings['name']['link'] = $_settings['link']['name'];

        // Make sure that the relationship names are synced
        $relationship_name = $_settings['relationship']['name'];
        $_settings['link']['relationship'] = $relationship_name;
        unset($_settings['relationship']['name']);

        // Add the built releationship
        $this->vardef['relationships'][$relationship_name] = $_settings['relationship'];

        // Add the fields
        foreach (array('id', 'name', 'link') as $type) {
            $this->addField($_settings[$type]['name'], $type, $_settings[$type]);
        }

        // Add the index if not set to non-array value
        if (isset($_settings['index']) && is_array($_settings['index'])) {
            $this->addIndex($_settings['id']['name'], $_settings['index']);
        }

        return $this;
    }

    /**
     * Makes changes to the vardefs.
     *
     * @param array $changes
     *
     * @return VardefModifier
     */
    public function change(array $changes)
    {
        $this->vardef = self::merge($this->vardef, $changes);

        return $this;
    }

    /**
     * Removes fields / properties this the vardef.
     *
     * @param array $values
     *
     * @return VardefModifier
     */
    public function remove(array $values)
    {
        static::_remove($values, $this->vardef);

        return $this;
    }

    /**
     * Changes the default field properties.
     *
     * @param array $field_defaults
     *
     * @return VardefModifier
     *
     * @throws Exception
     */
    public function defaults(array $field_defaults)
    {
        foreach ($field_defaults as $name => $field_default) {
            $this->setDefault($name, $field_default);
        }

        return $this;
    }

    /**
     * Adds fields based on a array definition.
     *
     * The keys in the array should be the field type
     * and the value an array of fields and definitions
     *
     * See VardefModifier::addField for supported field types
     *
     * @param array $types
     * @return VardefModifier
     * @throws Exception\InvalidDefinitionFormat
     * @internal param array $fields
     *
     */
    public function addFields(array $types)
    {
        foreach ($types as $type => $fields) {
            if (is_array($fields) && is_string($type)) {
                foreach ($fields as $name => $settings) {
                    if (is_int($name)) {
                        $name = $settings;
                        $this->addField($name, $type);
                    } else {
                        if (null === $settings) {
                            $settings = array ();
                        }

                        $this->addField($name, $type, $settings);
                    }
                }
            } else {
                throw new Exception\InvalidDefinitionFormat('Not Implemented');
            }
        }

        return $this;
    }

    /**
     * @param string $name
     * @param string $type
     * @param array  $settings
     *
     * @throws Exception\InvalidDefinitionFormat
     *
     * @return VardefModifier
     * @throws Exception
     */
    public function addField($name, $type, array $settings = array())
    {
        if (!is_string($name) || empty($name)) {
            throw new Exception\InvalidDefinitionFormat('Invalid type of name');
        }

        switch ($type) {
            case 'int':
            case 'integer':
                $this->addInt($name, $settings);
                break;
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
            case 'address':
                $this->addAddress($name, $settings);
                break;
            case 'flex_relate_link':
                $this->addFlexRelateLink($name, $settings);
                break;
            case 'flex_relate':
                $this->addFlexRelate($name, $settings);
                break;
            default:
                if ($this->hasDefault($type)) {
                    $this->addDefaultField($name, $this->getDefault($type), $settings);
                } else {
                    throw new Exception\InvalidDefinitionFormat("Invalid field type: '$type'");
                }
        }

        return $this;
    }

    /**
     * @param array $def
     */
    public function addDuplicateCheck(array $def)
    {
        $this->vardef['duplicate_check'] = $def;
    }

    /**
     * @param array $def
     */
    public function addAcls(array $def)
    {
        $this->vardef['acls'] = $def;
    }

    /**
     * @param array $def
     */
    public function addVisibility(array $def)
    {
        $this->vardef['visibility'] = $def;
    }

    /**
     * @param string $name
     *
     * @return bool
     * @return VardefModifier
     */
    public function hasField($name)
    {
        return isset($this->vardef['fields'][$name]);
    }

    /**
     * @return VardefModifier
     * @throws Exception
     */
    public function addCurrencyRelation()
    {
        if (!$this->hasField('currency_id')) {
            if ($this->version->getMajorVersion() < 7) {
                $this->addRelationship(
                    'Currencies',
                    array(
                        'id' => array(
                            'type' => 'currency_id',
                            'dbType' => 'id',
                            'group' => 'currency_id',
                            'default' => '-99',
                            'function' => array(
                                'name' => 'getCurrencyDropDown',
                                'returns' => 'html',
                            ),
                        ),
                        'name' => array(
                            'function' => array(
                                'name' => 'getCurrencyNameDropDown',
                                'returns' => 'html',
                            ),
                        ),
                    )
                );

                $this->addRelate('currency_symbol',
                    array(
                        'module' => 'Currencies',
                        'rname' => 'symbol',
                        'function' => array(
                            'name' => 'getCurrencySymbolDropDown',
                            'returns' => 'html',
                        ),
                    )
                );
            } else {
                $this->addRelationship(
                    'Currencies',
                    array(
                        'id' => array(
                            'type' => 'currency_id',
                            'dbType' => 'id',
                            'group' => 'currency_id',
                            'vname' => 'LBL_CURRENCY',
                            'function' => 'getCurrencies',
                            'function_bean' => 'Currencies',
                            'reportable' => false,
                            'default' => '-99',
                        ),
                        'name' => array(
                            'function' => 'getCurrencies',
                            'function_bean' => 'Currencies',
                            'studio' => false,
                        ),
                    )
                );

                $this->addRelate('currency_symbol',
                    array(
                        'module' => 'Currencies',
                        'rname' => 'symbol',
                        'function' => 'getCurrencySymbols',
                        'function_bean' => 'Currencies',
                        'studio' => false,
                    )
                );
            }
        }

        if ($this->version->getMajorVersion() >= 7 && !$this->hasField('base_rate')) {
            $this->addField('base_rate', 'decimal', array(
                'name' => 'base_rate',
                'vname' => 'LBL_CURRENCY_RATE',
                'type' => 'decimal',
                'len' => '26,6',
                'studio' => false,
                'default' => 1,
            ));
        }

        return $this;
    }

    /**
     * @return array
     */
    public function get()
    {
        $this->dictionary[$this->getDictionaryKey()] = $this->vardef;

        return $this->dictionary;
    }

    /**
     * @return string
     * @throws Exception\MissingTableName
     * @throws Exception\UnsupportedModule
     */
    private function getTableName()
    {
        $this->table_name = self::_getTableName($this->module_name, $this->dictionary);

        if (empty($this->table_name)) {
            throw new Exception\MissingTableName($this->module_name);
        }

        return $this->table_name;
    }

    /**
     * @param string $type
     * @return array
     * @throws Exception\UnsupportedDefaultsType
     */
    private function getDefault($type)
    {
        if (!isset($this->defaults[$type])) {
            throw new Exception\UnsupportedDefaultsType($type);
        }

        return $this->defaults[$type];
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    private function hasDefault($type)
    {
        return isset($this->defaults[$type]);
    }

    /**
     * @param string $name
     * @param array  $field_default
     *
     * @throws Exception\UnsupportedDefaultsType
     */
    private function setDefault($name, array $field_default)
    {
        if (!isset($this->defaults[$name])) {
            throw new Exception\UnsupportedDefaultsType($name);
        }

        $this->defaults[$name] = array_merge($this->defaults[$name], $field_default);
    }

    /**
     * @param string $name
     * @param array  $settings
     *
     * @return VardefModifier
     * @throws Exception
     */
    private function addName($name, array $settings)
    {
        $default = $this->getDefault('name');

        if ($this->version->getMajorVersion() >= 7) {
            $default['sort_on'] = $default['rname'];
        }

        return $this->addRelate($name, self::merge($default, $settings));
    }

    /**
     * @param string $name
     * @param array $settings
     * @return VardefModifier
     * @throws Exception
     */
    private function addRelate($name, array $settings)
    {
        if (!isset($settings['module'])) {
            throw new Exception\InvalidDefinitionFormat('Missing module');
        }

        $default = array(
            'rname' => $name,
            'table' => self::_getTableName($settings['module'], $this->dictionary),
            'id_name' => strtolower(self::getObjectName($settings['module'])).'_id',
        );

        $default = self::merge($this->getDefault('relate'), $default);

        if ($this->version->getMajorVersion() >= 7) {
            $default['sort_on'] = $default['rname'];
        }

        return $this->addDefaultField(
            $name, $default, $settings
        );
    }

    /**
     * @param string $name
     * @param array  $settings
     *
     * @return VardefModifier
     * @throws Exception
     */
    private function addAddress($name, array $settings = array())
    {
        $defaults = self::merge(
            $this->getDefault('address'),
            $settings
        );

        $all = $defaults['all'];

        unset($defaults['all']);

        if (empty($all['group'])) {
            if ($name === 'address') {
                $all['group'] = $name;
            } else {
                $all['group'] = $name.'_address';
            }
        }

        foreach ($defaults as $field_name => $field_settings) {
            if (is_array($field_settings)) {
                $field_settings = self::merge($all, $field_settings);
                $this->addField(
                    $all['group'].'_'.$field_name, $field_settings['type'], $field_settings
                );
            }
        }

        return $this;
    }

    /**
     * @param string $name
     * @param array
     *
     * @return VardefModifier
     * @throws Exception\InvalidDefinitionFormat
     * @throws Exception\UnsupportedDefaultsType
     * @throws Exception
     */
    private function addCurrency($name, array $settings = array())
    {
        $template = $this->getDefault('currency');

        $baseSettings = $settings;
        $baseTemplate = $this->getDefault('currency_base');

        $baseSettings['group'] = $name;
        if (!empty($baseSettings['required'])) {
            $baseSettings['required'] = false;
        }

        if ($this->version->getMajorVersion() >= 7) {
            $template['convertToBase'] = true;
            $template['showTransactionalAmount'] = true;
            $template['validation'] = array();
            $template['related_fields'] = array('currency_id', 'base_rate');

            $baseTemplate['readonly'] = true;
            $baseTemplate['is_base_currency'] = true;
            $baseTemplate['related_fields'] = array('currency_id', 'base_rate');

            $baseSettings['calculated'] = true;
            $baseSettings['enforced'] = true;
            $baseSettings['formula'] = "ifElse(isNumeric(\$$name), currencyDivide(\$$name, \$base_rate), \"\")";
        }

        return $this->
                addCurrencyRelation()->
                addDefaultField($name, $template, $settings)->
                addDefaultField($name.'_usdollar', $baseTemplate, $baseSettings);
    }

    /**
     * Adds a link field.
     *
     * @param string $name
     * @param array  $settings
     *
     * @return VardefModifier
     * @throws Exception
     */
    private function addLink($name, array $settings = array())
    {
        if (empty($settings['module'])) {
            $settings['module'] = $name;
            $name = strtolower($name);
        }

        $object_name = self::getObjectName($settings['module']);
        $relationship_names = array($object_name);

        if (!empty($settings['relationship_name'])) {
            $relationship_names[] = $settings['relationship_name'];
            // No need to store this in the vardef...
            unset($settings['relationship_name']);
        }

        $relationship_names[] = $this->module_name;

        $setSide = !isset($settings['side']);

        $def = array_merge(
            $this->getDefault('link'), array(
                'bean_name' => $object_name,
                'relationship' => strtolower(implode('_', $relationship_names)),
            ), $settings
        );

        if ($setSide && false !== strpos('_flex_relate_', $def['relationship'])) {
            $def['side'] = 'right';
        }

        $this->addFieldToVardef($name, $def);

        return $this;
    }

    /**
     * @param string $name
     * @param array  $settings
     *
     * @return VardefModifier
     * @throws Exception
     */
    private function addEnum($name, array $settings = array())
    {
        return $this->addEnumLike($name, $this->getDefault('enum'), $settings);
    }

    /**
     * @param string $name
     * @param array  $settings
     *
     * @return VardefModifier
     * @throws Exception
     */
    private function addMultienum($name, array $settings = array())
    {
        return $this->addEnumLike($name, $this->getDefault('multienum'), $settings);
    }

    /**
     * @param string $name
     * @param array  $settings
     *
     * @return VardefModifier
     * @throws Exception
     */
    public function addInt($name, array $settings)
    {
        $default = $this->getDefault('int');

        if (!empty($settings['auto_increment'])) {
            $default['readonly'] = true;
            $indexSettings = !empty($settings['index']) ? $settings['index'] : array();
            $this->addIndex($name, $indexSettings);
        }

        return $this->addDefaultField(
            $name,
            $default,
            $settings
        );
    }

    /**
     * @param string $name
     * @param array  $settings
     * @param array  $default
     *
     * @return VardefModifier
     * @throws Exception
     */
    private function addEnumLike($name, array $default, array $settings)
    {
        return $this->addDefaultField(
            $name, array('options' => strtolower($this->module_name.'_'.$name).'_list'), $default, $settings
        );
    }

    /**
     * @param string $name
     *
     * @return VardefModifier
     * @throws Exception
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
     * @throws Exception
     */
    private function getBase()
    {
        return $this->getDefault('_base');
    }

    /**
     * @param string $name
     * @param array $definition
     *
     * @return array
     */
    private function addFieldToVardef($name, array $definition)
    {
        $this->vardef['fields'][$name] = array_merge(array(
            'name' => $name,
            'vname' => $this->getVName($name),
        ), $definition);
    }

    /**
     * @param string $name
     * @return string
     */
    private function getVName($name)
    {
        return 'LBL_'.strtoupper($name);
    }
}
