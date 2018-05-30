<?php

namespace admintests\admin\ngrest\base;

use admintests\AdminTestCase;
use admintests\data\stubs\StubPlugin;
use luya\admin\ngrest\plugins\Text;

class PluginTest extends AdminTestCase
{
    public $plugin;
    public $plugini18n;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->plugin = new StubPlugin(['name' => 'myField', 'alias' => 'Stub Label', 'i18n' => false]);
        $this->plugini18n = new StubPlugin(['name' => 'myField', 'alias' => 'Stub Label', 'i18n' => true]);
    }
    public function testInit()
    {
        $this->expectException('luya\Exception');
        $plugin = new StubPlugin();
    }
    
    public function testGetServiceName()
    {
        $this->assertSame('service.myField.fooBar', $this->plugin->getServiceName('fooBar'));
    }
    
    public function testNgShowCondition()
    {
        // expression
        $plugin = new Text(['condition' => '{feld2}=1', 'name' => 'feld1', 'i18n' => false, 'alias' => 'Feld 1 Label']);
        $this->assertSame('data.create.feld2=1', $plugin->getNgShowCondition('data.create.feld1'));
        
        // no condition
        $plugin = new Text(['name' => 'feld1', 'i18n' => false, 'alias' => 'Feld 1 Label']);
        $this->assertSame('', $plugin->getNgShowCondition('data.create.feld1'));
        
        // condition without field
        $plugin = new Text(['condition' => '0=1', 'name' => 'feld1', 'i18n' => false, 'alias' => 'Feld 1 Label']);
        $this->assertSame('0=1', $plugin->getNgShowCondition('data.create.feld1'));
        
        // condition without multiple fields
        $plugin = new Text(['condition' => '{feld1} && {feld3}', 'name' => 'feld1', 'i18n' => false, 'alias' => 'Feld 1 Label']);
        $this->assertSame('data.create.feld1 && data.create.feld3', $plugin->getNgShowCondition('data.create.feld1'));
    }
}
