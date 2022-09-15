<?php

namespace luya\admin\tests\admin\base;

use admintests\AdminTestCase;
use luya\admin\base\CheckboxArrayProperty;

class CheckboxArrayPropertyTest extends AdminTestCase
{
    public function testDefaultAdminValue()
    {
        $prop = new StubCheckoxArrayProperty();

        $this->assertSame(false, $prop->defaultValue());

        $this->assertSame(null, $prop->getAdminValue());
        $this->assertNull($prop->getValue());

        $this->assertSame([
            'items' => [
                ['label' => 'bar', 'value' => 'foo'],
                ['label' => 'foo', 'value' => 'baz'],
            ]
        ], $prop->options());
    }
}

class StubCheckoxArrayProperty extends CheckboxArrayProperty
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
