<?php

namespace luya\admin\tests\admin\apis;

use admintests\AdminModelTestCase;
use luya\admin\apis\StorageController;

class StorageControllerTest extends AdminModelTestCase
{
    public function testFlushApiCache()
    {
        $ctrl = new StorageController('id', $this->app);

        $this->assertEmpty($this->invokeMethod($ctrl, 'flushApiCache'));

    }
}