<?php

require_once __DIR__ . '/VardefModifier.php';

/**
 * @author Emil Kilhage
 */
class VardefModifier_Test extends PHPUnit_Framework_TestCase
{

    private $module_name;
    private $object_name;

    protected function setUp()
    {
        global $beanList, $dictionary;
        $this->module_name = "_MyModules";
        $this->object_name = "_MyModule";
        $beanList[$this->module_name] = $this->object_name;
        $dictionary[$this->object_name] = array (
            'favorites' => true,
            'fields' => array (),
            'indices' => array (),
            'relationships' => array (),
        );
    }

    protected function tearDown()
    {
        global $beanList, $dictionary;
        unset($beanList[$this->module_name]);
        unset($dictionary[$this->object_name]);
    }

    private function create()
    {
        global $dictionary;
        return new VardefModifier($this->module_name, $dictionary);
    }

    public function test_Varchar()
    {
        $real_dic = array (
            'favorites' => true,
            'fields' => array (
                'field1' => array (
                    'name' => 'field1',
                    'vname' => 'LBL_FIELD1',
                    'required' => false,
                    'reportable' => true,
                    'audited' => true,
                    'importable' => 'true',
                    'massupdate' => false,
                    'type' => 'varchar',
                    'len' => '255',
                ),
            ),
            'indices' => array (),
            'relationships' => array (),
        );
        $m = $this->create();
        $m->addField('field1', 'varchar');
        $dic = $m->get();
        $this->assertEquals($real_dic, $dic[$this->object_name]);
        $m = $this->create();
        $m->add(array (
            'fields' => array (
                'varchar' => array (
                    'field1'
                )
            )
        ));
        $dic = $m->get();
        $this->assertEquals($real_dic, $dic[$this->object_name]);
        $real_dic['fields']['field1']['len'] = '20';
        $m = $this->create();
        $m->addField('field1', 'varchar', array (
            'len' => '20'
        ));
        $dic = $m->get();
        $this->assertEquals($real_dic, $dic[$this->object_name]);
        $real_dic['fields']['field1']['len'] = '30';
        $real_dic['fields']['field1']['audited'] = false;
        $m = $this->create();
        $m->add(array (
            'fields' => array (
                'varchar' => array (
                    'field1' => array (
                        'len' => '30',
                        'audited' => false
                    )
                )
            )
        ));
        $dic = $m->get();
        $this->assertEquals($real_dic, $dic[$this->object_name]);
    }

    public function test_Bool()
    {
        $real_dic = array (
            'favorites' => true,
            'fields' => array (
                'field1' => array (
                    'name' => 'field1',
                    'vname' => 'LBL_FIELD1',
                    'required' => false,
                    'reportable' => true,
                    'audited' => true,
                    'importable' => 'true',
                    'massupdate' => false,
                    'type' => 'bool',
                ),
            ),
            'indices' => array (),
            'relationships' => array (),
        );
        $m = $this->create();
        $m->addField('field1', 'bool');
        $dic = $m->get();
        $this->assertEquals($real_dic, $dic[$this->object_name]);
        $m = $this->create();
        $m->add(array (
            'fields' => array (
                'bool' => array (
                    'field1'
                )
            )
        ));
        $dic = $m->get();
        $this->assertEquals($real_dic, $dic[$this->object_name]);
    }

    public function test_Text()
    {
        $real_dic = array (
            'favorites' => true,
            'fields' => array (
                'field1' => array (
                    'name' => 'field1',
                    'vname' => 'LBL_FIELD1',
                    'required' => false,
                    'reportable' => true,
                    'audited' => true,
                    'importable' => 'true',
                    'massupdate' => false,
                    'type' => 'text',
                ),
            ),
            'indices' => array (),
            'relationships' => array (),
        );
        $m = $this->create();
        $m->addField('field1', 'text');
        $dic = $m->get();
        $this->assertEquals($real_dic, $dic[$this->object_name]);
        $m = $this->create();
        $m->add(array (
            'fields' => array (
                'text' => array (
                    'field1'
                )
            )
        ));
        $dic = $m->get();
        $this->assertEquals($real_dic, $dic[$this->object_name]);
    }

    public function test_Date()
    {
        $real_dic = array (
            'favorites' => true,
            'fields' => array (
                'field1' => array (
                    'name' => 'field1',
                    'vname' => 'LBL_FIELD1',
                    'required' => false,
                    'reportable' => true,
                    'audited' => true,
                    'importable' => 'true',
                    'massupdate' => false,
                    'type' => 'date',
                ),
            ),
            'indices' => array (),
            'relationships' => array (),
        );
        $m = $this->create();
        $m->addField('field1', 'date');
        $dic = $m->get();
        $this->assertEquals($real_dic, $dic[$this->object_name]);
        $m = $this->create();
        $m->add(array (
            'fields' => array (
                'date' => array (
                    'field1'
                )
            )
        ));
        $dic = $m->get();
        $this->assertEquals($real_dic, $dic[$this->object_name]);
    }

    public function test_Decimal()
    {
        $real_dic = array (
            'favorites' => true,
            'fields' => array (
                'field1' => array (
                    'name' => 'field1',
                    'vname' => 'LBL_FIELD1',
                    'required' => false,
                    'reportable' => true,
                    'audited' => true,
                    'importable' => 'true',
                    'massupdate' => false,
                    'type' => 'decimal',
                    'len' => '26,6',
                ),
            ),
            'indices' => array (),
            'relationships' => array (),
        );
        $m = $this->create();
        $m->addField('field1', 'decimal');
        $dic = $m->get();
        $this->assertEquals($real_dic, $dic[$this->object_name]);
        $m = $this->create();
        $m->add(array (
            'fields' => array (
                'decimal' => array (
                    'field1'
                )
            )
        ));
        $dic = $m->get();
        $this->assertEquals($real_dic, $dic[$this->object_name]);
    }

    public function test_Image()
    {
        $real_dic = array (
            'favorites' => true,
            'fields' => array (
                'field1' => array (
                    'name' => 'field1',
                    'vname' => 'LBL_FIELD1',
                    'required' => false,
                    'reportable' => true,
                    'audited' => true,
                    'importable' => 'true',
                    'massupdate' => false,
                    'type' => 'image',
                    'dbType' => "varchar",
                    'height' => '100'
                ),
            ),
            'indices' => array (),
            'relationships' => array (),
        );
        $m = $this->create();
        $m->addField('field1', 'image');
        $dic = $m->get();
        $this->assertEquals($real_dic, $dic[$this->object_name]);
        $m = $this->create();
        $m->add(array (
            'fields' => array (
                'image' => array (
                    'field1'
                )
            )
        ));
        $dic = $m->get();
        $this->assertEquals($real_dic, $dic[$this->object_name]);
    }

    public function test_DateTimeCombo()
    {
        $real_dic = array (
            'favorites' => true,
            'fields' => array (
                'field1' => array (
                    'name' => 'field1',
                    'vname' => 'LBL_FIELD1',
                    'required' => false,
                    'reportable' => true,
                    'audited' => true,
                    'importable' => 'true',
                    'massupdate' => false,
                    'type' => 'datetimecombo',
                    'dbType' => 'datetime'
                ),
            ),
            'indices' => array (),
            'relationships' => array (),
        );
        $m = $this->create();
        $m->addField('field1', 'datetimecombo');
        $dic = $m->get();
        $this->assertEquals($real_dic, $dic[$this->object_name]);
        $m = $this->create();
        $m->add(array (
            'fields' => array (
                'datetimecombo' => array (
                    'field1'
                )
            )
        ));
        $dic = $m->get();
        $this->assertEquals($real_dic, $dic[$this->object_name]);
    }

    public function test_Url()
    {
        $real_dic = array (
            'favorites' => true,
            'fields' => array (
                'field1' => array (
                    'name' => 'field1',
                    'vname' => 'LBL_FIELD1',
                    'required' => false,
                    'reportable' => true,
                    'audited' => true,
                    'importable' => 'true',
                    'massupdate' => false,
                    'type' => 'url',
                    'dbType' => 'varchar'
                ),
            ),
            'indices' => array (),
            'relationships' => array (),
        );
        $m = $this->create();
        $m->addField('field1', 'url');
        $dic = $m->get();
        $this->assertEquals($real_dic, $dic[$this->object_name]);
        $m = $this->create();
        $m->add(array (
            'fields' => array (
                'url' => array (
                    'field1'
                )
            )
        ));
        $dic = $m->get();
        $this->assertEquals($real_dic, $dic[$this->object_name]);
    }

    public function test_datetime()
    {
        $real_dic = array (
            'favorites' => true,
            'fields' => array (
                'field1' => array (
                    'name' => 'field1',
                    'vname' => 'LBL_FIELD1',
                    'required' => false,
                    'reportable' => true,
                    'audited' => true,
                    'importable' => 'true',
                    'massupdate' => false,
                    'type' => 'datetime',
                ),
            ),
            'indices' => array (),
            'relationships' => array (),
        );
        $m = $this->create();
        $m->addField('field1', 'datetime');
        $dic = $m->get();
        $this->assertEquals($real_dic, $dic[$this->object_name]);
        $m = $this->create();
        $m->add(array (
            'fields' => array (
                'datetime' => array (
                    'field1'
                )
            )
        ));
        $dic = $m->get();
        $this->assertEquals($real_dic, $dic[$this->object_name]);
    }

    public function test_float()
    {
        $real_dic = array (
            'favorites' => true,
            'fields' => array (
                'field1' => array (
                    'name' => 'field1',
                    'vname' => 'LBL_FIELD1',
                    'required' => false,
                    'reportable' => true,
                    'audited' => true,
                    'importable' => 'true',
                    'massupdate' => false,
                    'type' => 'float',
                ),
            ),
            'indices' => array (),
            'relationships' => array (),
        );
        $m = $this->create();
        $m->addField('field1', 'float');
        $dic = $m->get();
        $this->assertEquals($real_dic, $dic[$this->object_name]);
        $m = $this->create();
        $m->add(array (
            'fields' => array (
                'float' => array (
                    'field1'
                )
            )
        ));
        $dic = $m->get();
        $this->assertEquals($real_dic, $dic[$this->object_name]);
    }

    public function test_phone()
    {
        $real_dic = array (
            'favorites' => true,
            'fields' => array (
                'field1' => array (
                    'name' => 'field1',
                    'vname' => 'LBL_FIELD1',
                    'required' => false,
                    'reportable' => true,
                    'audited' => true,
                    'importable' => 'true',
                    'massupdate' => false,
                    'type' => 'phone',
                    'dbType' => 'varchar',
                    'len' => 100
                ),
            ),
            'indices' => array (),
            'relationships' => array (),
        );
        $m = $this->create();
        $m->addField('field1', 'phone');
        $dic = $m->get();
        $this->assertEquals($real_dic, $dic[$this->object_name]);
        $m = $this->create();
        $m->add(array (
            'fields' => array (
                'phone' => array (
                    'field1'
                )
            )
        ));
        $dic = $m->get();
        $this->assertEquals($real_dic, $dic[$this->object_name]);
    }

    public function test_id()
    {
        $real_dic = array (
            'favorites' => true,
            'fields' => array (
                'field1' => array (
                    'name' => 'field1',
                    'vname' => 'LBL_FIELD1',
                    'required' => false,
                    'reportable' => true,
                    'audited' => true,
                    'importable' => 'true',
                    'massupdate' => false,
                    'type' => 'id',
                ),
            ),
            'indices' => array (),
            'relationships' => array (),
        );
        $m = $this->create();
        $m->addField('field1', 'id');
        $dic = $m->get();
        $this->assertEquals($real_dic, $dic[$this->object_name]);
        $m = $this->create();
        $m->add(array (
            'fields' => array (
                'id' => array (
                    'field1'
                )
            )
        ));
        $dic = $m->get();
        $this->assertEquals($real_dic, $dic[$this->object_name]);
    }

    public function test_currency()
    {
        $real_dic = array (
            'favorites' => true,
            'fields' => array (
                'currency_id' => array (
                    'name' => 'currency_id',
                    'vname' => 'LBL_CURRENCY_ID',
                    'required' => false,
                    'reportable' => true,
                    'audited' => true,
                    'importable' => 'true',
                    'massupdate' => false,
                    'type' => 'id',
                    'group' => 'currency_id',
                    'function' => array (
                        'name' => 'getCurrencyDropDown',
                        'returns' => 'html',
                    ),
                ),
                'currency_name' => array (
                    'name' => 'currency_name',
                    'vname' => 'LBL_CURRENCY_NAME',
                    'required' => false,
                    'reportable' => true,
                    'audited' => true,
                    'importable' => 'true',
                    'massupdate' => false,
                    'module' => 'Currencies',
                    'rname' => 'name',
                    'function' => array (
                        'name' => 'getCurrencyNameDropDown',
                        'returns' => 'html',
                    ),
                    'table' => 'currencies',
                    'id_name' => 'currency_id',
                    'source' => 'non-db',
                    'type' => 'relate',
                ),
                'currency_symbol' => array (
                    'name' => 'currency_symbol',
                    'vname' => 'LBL_CURRENCY_SYMBOL',
                    'required' => false,
                    'reportable' => true,
                    'audited' => true,
                    'importable' => 'true',
                    'massupdate' => false,
                    'module' => 'Currencies',
                    'rname' => 'symbol',
                    'function' => array (
                        'name' => 'getCurrencySymbolDropDown',
                        'returns' => 'html',
                    ),
                    'table' => 'currencies',
                    'id_name' => 'currency_id',
                    'source' => 'non-db',
                    'type' => 'relate',
                ),
                'field1' => array (
                    'name' => 'field1',
                    'vname' => 'LBL_FIELD1',
                    'required' => false,
                    'reportable' => true,
                    'audited' => true,
                    'importable' => 'true',
                    'massupdate' => false,
                    'type' => 'currency',
                    'len' => '26,6',
                    'dbType' => 'decimal',
                ),
                'field1_usdollar' => array (
                    'name' => 'field1_usdollar',
                    'vname' => 'LBL_FIELD1_USDOLLAR',
                    'required' => false,
                    'reportable' => true,
                    'audited' => true,
                    'importable' => 'true',
                    'massupdate' => false,
                    'type' => 'currency',
                    'len' => '26,6',
                    'dbType' => 'decimal',
                    'group' => 'field1',
                ),
            ),
            'indices' => array (),
            'relationships' => array (),
        );
        $m = $this->create();
        $m->addField('field1', 'currency');
        $dic = $m->get();
        $this->assertEquals($real_dic, $dic[$this->object_name]);
        $m = $this->create();
        $m->add(array (
            'fields' => array (
                'currency' => array (
                    'field1'
                )
            )
        ));
        $dic = $m->get();
        $this->assertEquals($real_dic, $dic[$this->object_name]);
        $real_dic['fields']['field1']['len'] = '20,1';
        $real_dic['fields']['field1_usdollar']['len'] = '20,1';
        $m->add(array (
            'fields' => array (
                'currency' => array (
                    'field1' => array (
                        'len' => '20,1'
                    )
                )
            )
        ));
        $dic = $m->get();
        $this->assertEquals($real_dic, $dic[$this->object_name]);
    }

    public function test_enum()
    {
        $real_dic = array (
            'favorites' => true,
            'fields' => array (
                'field1' => array (
                    'name' => 'field1',
                    'vname' => 'LBL_FIELD1',
                    'required' => false,
                    'reportable' => true,
                    'audited' => true,
                    'importable' => 'true',
                    'massupdate' => false,
                    'type' => 'enum',
                    'options' => 'field1_dom'
                ),
            ),
            'indices' => array (),
            'relationships' => array (),
        );
        $m = $this->create();
        $m->addField('field1', 'enum');
        $dic = $m->get();
        $this->assertEquals($real_dic, $dic[$this->object_name]);
        $m = $this->create();
        $m->add(array (
            'fields' => array (
                'enum' => array (
                    'field1'
                )
            )
        ));
        $dic = $m->get();
        $this->assertEquals($real_dic, $dic[$this->object_name]);
    }

    public function test_multienum()
    {
        $real_dic = array (
            'favorites' => true,
            'fields' => array (
                'field1' => array (
                    'name' => 'field1',
                    'vname' => 'LBL_FIELD1',
                    'required' => false,
                    'reportable' => true,
                    'audited' => true,
                    'importable' => 'true',
                    'massupdate' => false,
                    'type' => 'multienum',
                    'options' => 'field1_dom',
                    'isMultiSelect' => true
                ),
            ),
            'indices' => array (),
            'relationships' => array (),
        );
        $m = $this->create();
        $m->addField('field1', 'multienum');
        $dic = $m->get();
        $this->assertEquals($real_dic, $dic[$this->object_name]);
        $m = $this->create();
        $m->add(array (
            'fields' => array (
                'multienum' => array (
                    'field1'
                )
            )
        ));
        $dic = $m->get();
        $this->assertEquals($real_dic, $dic[$this->object_name]);
        $real_dic['fields']['field1']['isMultiSelect'] = false;
        $m->add(array (
            'fields' => array (
                'multienum' => array (
                    'field1' => array (
                        'isMultiSelect' => false
                    )
                )
            )
        ));
        $dic = $m->get();
        $this->assertEquals($real_dic, $dic[$this->object_name]);
    }

    public function test_relate()
    {
        $real_dic = array (
            'favorites' => true,
            'fields' => array (
                'field1' => array (
                    'name' => 'field1',
                    'vname' => 'LBL_FIELD1',
                    'required' => false,
                    'reportable' => true,
                    'audited' => true,
                    'importable' => 'true',
                    'massupdate' => false,
                    'rname' => 'field1',
                    'table' => 'accounts',
                    'id_name' => 'account_id',
                    'source' => 'non-db',
                    'type' => 'relate',
                    'module' => 'Accounts'
                ),
            ),
            'indices' => array (),
            'relationships' => array (),
        );
        $m = $this->create();
        $m->addField('field1', 'relate', array ('module' => 'Accounts'));
        $dic = $m->get();
        $this->assertEquals($real_dic, $dic[$this->object_name]);
        $m = $this->create();
        $m->add(array (
            'fields' => array (
                'relate' => array (
                    'field1' => array (
                        'module' => 'Accounts'
                    )
                )
            )
        ));
        $dic = $m->get();
        $this->assertEquals($real_dic, $dic[$this->object_name]);
        $real_dic['fields']['field1']['rname'] = 'field2';
        $m->add(array (
            'fields' => array (
                'relate' => array (
                    'field1' => array (
                        'module' => 'Accounts',
                        'rname' => 'field2'
                    )
                )
            )
        ));
        $dic = $m->get();
        $this->assertEquals($real_dic, $dic[$this->object_name]);
    }

}
