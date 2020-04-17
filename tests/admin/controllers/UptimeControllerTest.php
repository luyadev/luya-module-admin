<?php

namespace luya\admin\tests\admin\controllers;

use admintests\AdminModelTestCase;
use luya\admin\controllers\UptimeController;

class UptimeControllerTest extends AdminModelTestCase
{
    public function testIndex()
    {
        $ctrl = new UptimeController('uptime', $this->app);

        $response = $ctrl->actionIndex();

        $this->assertArrayHasKey('db', $response);
        $this->assertTrue($response['db']);
    }
}
