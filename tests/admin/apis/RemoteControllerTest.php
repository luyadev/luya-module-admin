<?php

namespace luya\admin\tests\admin\apis;

use admintests\AdminModelTestCase;
use luya\admin\apis\RemoteController;
use luya\testsuite\traits\AdminDatabaseTableTrait;
use yii\web\ForbiddenHttpException;

class RemoteControllerTest extends AdminModelTestCase
{
    use AdminDatabaseTableTrait;

    public function testIndexWrongToken()
    {
        $ctrl = new RemoteController('ctrl', $this->app);
        $this->expectException(ForbiddenHttpException::class);
        $ctrl->actionIndex('123');
    }

    public function testIndexContent()
    {
        $this->createAdminUserOnlineFixture();
        $this->app->remoteToken = 'foobar';
        $ctrl = new RemoteController('ctrl', $this->app->getModule('admin'));
        $r = $ctrl->actionIndex(sha1('foobar'));

        $this->assertArrayHasKey('yii_version', $r);
        $this->assertArrayHasKey('packages', $r);
    }

    public function testGenerateOpenApi()
    {
        $this->app->remoteToken = 'foobar';
        $ctrl = new RemoteController('ctrl', $this->app->getModule('admin'));
        $this->assertNotEmpty($ctrl->actionOpenapi(sha1('foobar')));
    }
}
