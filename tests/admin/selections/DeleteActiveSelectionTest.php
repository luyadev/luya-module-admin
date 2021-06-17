<?php

namespace luya\admin\tests\admin\selections;

use admintests\AdminModelTestCase;
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
}