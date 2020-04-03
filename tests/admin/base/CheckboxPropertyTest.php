<?php

namespace luya\admin\tests\admin\base;

use admintests\AdminTestCase;
use luya\admin\base\CheckboxProperty;

class CheckboxPropertyTest extends AdminTestCase
{
    public function testDefaultAdminValue()
    {
        $prop = new StubCheckoxProperty([
            'value' => 1,
        ]);

        $this->assertSame(0, $prop->defaultValue());

        $this->assertSame(1, $prop->getAdminValue());
        $this->assertTrue($prop->getValue());

        $emptyProp = new StubCheckoxProperty();

        $this->assertSame(null, $emptyProp->getAdminValue());
        $this->assertFalse($emptyProp->getValue());
    }
}

class StubCheckoxProperty extends CheckboxProperty
{
    public function varName()
    {
        return 'varname';
    }

    public function label()
    {
        return 'label';
    }
}
