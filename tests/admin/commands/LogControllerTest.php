<?php

namespace admintests\admin\commands;

use admintests\AdminConsoleSqLiteTestCase;
use luya\admin\commands\LogController;

class LogControllerTest extends AdminConsoleSqLiteTestCase
{
    /**
     * @return LogController
     */
    private function createCommand()
    {
        $this->app->mute = 1;
        return new LogController('log', $this->app);
    }

    public function testTables()
    {
        $ctrl = $this->createCommand();


        $this->assertFalse($this->invokeMethod($ctrl, 'validateTables', ['foo']));
        $this->assertFalse($this->invokeMethod($ctrl, 'validateTables', [null]));
        $this->assertTrue($this->invokeMethod($ctrl, 'validateTables', ['all']));
        $this->assertFalse($this->invokeMethod($ctrl, 'validateTables', ['cms_log,butthisiswrong']));
        $this->assertTrue($this->invokeMethod($ctrl, 'validateTables', ['admin_ngrest_log,cms_log']));
    }

    public function testValidateYear()
    {
        $ctrl = $this->createCommand();
        $this->assertTrue($this->invokeMethod($ctrl, 'validateYears'));
        $ctrl->years = -1;
        $this->assertFalse($this->invokeMethod($ctrl, 'validateYears'));
    }

    public function testValidateRows()
    {
        $ctrl = $this->createCommand();
        $this->assertTrue($this->invokeMethod($ctrl, 'validateRows'));
        $ctrl->rows = -1;
        $this->assertFalse($this->invokeMethod($ctrl, 'validateRows'));
    }
}
