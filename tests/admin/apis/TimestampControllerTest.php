<?php

namespace luya\admin\tests\admin\apis;

use admintests\AdminModelTestCase;
use luya\admin\apis\TimestampController;
use luya\testsuite\scopes\PermissionScope;

class TimestampControllerTest extends AdminModelTestCase
{
    public function testIndexAction()
    {
        PermissionScope::run($this->app, function (PermissionScope $scope) {
            $this->createAdminUserOnlineFixture([
                'user1' => [
                    'id' => 1,
                    'user_id' => 1,
                ]
            ]);
            $ctrl = new TimestampController('timestamp', $scope->getApp()->getModule('admin'));
            $scope->loginUser();
            $response = $scope->runControllerAction($ctrl, 'index');

            $this->assertArrayHasKey('notifications', $response);
            $this->assertArrayHasKey('lastKeyStroke', $response);
            $this->assertArrayHasKey('idleSeconds', $response);
            $this->assertArrayHasKey('idlePercentage', $response);
            $this->assertArrayHasKey('idleStrokeDashoffset', $response);
            $this->assertArrayHasKey('useronline', $response);
            $this->assertArrayHasKey('forceReload', $response);
            $this->assertArrayHasKey('locked', $response);
        }, function (PermissionScope $config) {
            $config->userFixtureData = ['is_api_user' => 0];
        });
    }
}
