<?php

namespace luya\admin\tests\admin\apis;

use admintests\AdminModelTestCase;
use luya\admin\apis\GroupController;
use luya\testsuite\scopes\PermissionScope;

class GroupControllerTest extends AdminModelTestCase
{
    public function testMeGroups()
    {
        PermissionScope::run($this->app, function (PermissionScope $scope) {
            $scope->createAndAllowApi('api-admin-group');

            $ctrl = new GroupController('api-admin-group', $this->app->getModule('admin'));

            $response = $scope->runControllerAction($ctrl, 'me');

            $this->assertEmpty($response);
        });
    }
}
