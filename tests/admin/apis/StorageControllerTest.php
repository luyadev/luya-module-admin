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

    public function testPermissionBasedAction()
    {
        $ctrl = new StorageController('id', $this->app);

        $this->permissionScope(function($scope) use($ctrl) {
            $scope->allowRoute('mytestapp/id/replace-file');
            $response = $scope->runControllerAction($ctrl, 'replace-file');
            // compare response
        });

        $this->permissionScope(function($scope) use ($ctrl) {
            // forbidden 
            $response = $scope->runControllerAction($ctrl, 'replace-file');
        });
    }
}