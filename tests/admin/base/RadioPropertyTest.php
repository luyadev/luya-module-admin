<?php

namespace luya\admin\tests\admin\base;

use admintests\AdminTestCase;
use luya\admin\base\RadioProperty;

class RadioPropertyTest extends AdminTestCase
{
    public function testDefaultAdminValue()
    {
        $prop = new StubRadioProperty();

        $this->assertSame(false, $prop->defaultValue());

        $this->assertSame(null, $prop->getAdminValue());
        $this->assertNull($prop->getValue());

        $this->assertSame([
            ['label' => 'bar', 'value' => 'foo'],
            ['label' => 'foo', 'value' => 'baz'],
        ], $prop->options());
    }
}

class StubRadioProperty extends RadioProperty
{
    public function varName()
    {
        return 'varname';
    }

    public function label()
    {
        return 'label';
    }

    public function items()
    {
        return [
            'foo' => 'bar',
            'baz' => 'foo',
        ];
    }
}
