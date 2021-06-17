<?php

namespace luya\admin\tests\admin\selections;

use admintests\AdminModelTestCase;
use luya\admin\ngrest\base\ActiveSelection;
use luya\admin\selections\DeleteActiveSelection;

class DeleteActiveSelectionTest extends AdminModelTestCase
{
    public function testHandle()
    {
        $obj = new DeleteActiveSelection();
        $r = $obj->handle([]);

        $this->assertTrue($r['success']);
        $this->assertFalse($obj->sendError('foo')['success']);
    }

    public function testExternalHandle()
    {
        $obj = new ActiveSelection();
        $obj->action = function(array $items, ActiveSelection $context) {
            return true;
        };
        $r = $obj->handle([]);

        $this->assertTrue($r['success']);

        $obj->action = function(array $items, ActiveSelection $context) {
            return false;
        };
        $r = $obj->handle([]);

        $this->assertTrue($r['error']);

        $obj->action = function(array $items, ActiveSelection $context) {
            return ['foo' => 'bar'];
        };
        $r = $obj->handle([]);

        $this->assertSame(['foo' => 'bar'], $r);
    }
}