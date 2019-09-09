<?php

namespace luya\admin\tests\admin\base;

use admintests\AdminTestCase;
use luya\admin\tests\data\properties\MyTestProperty;

class PropertyTest extends AdminTestCase
{
    public function testIdentifier()
    {
        $this->assertSame('barfoo', MyTestProperty::identifier());
        $this->assertSame('barfoo', (new MyTestProperty())->varName());
    }
}
