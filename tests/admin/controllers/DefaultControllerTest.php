<?php

namespace luya\admin\tests\admin\controllers;

use admintests\AdminModelTestCase;
use luya\admin\controllers\DefaultController;
use luya\admin\controllers\DefaultController as ControllersDefaultController;
use luya\admin\models\UserDevice;
use luya\testsuite\fixtures\NgRestModelFixture;
use yii\web\Response;

class DefaultControllerTest extends AdminModelTestCase
{
    public function testLogout()
    {
        new NgRestModelFixture(['modelClass' => UserDevice::class]);
        $default = new DefaultController('default', $this->app->getModule('admin'));
        $response = $default->actionLogout();

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testViewFilesForSyntaxError()
    {
        $this->assertNotNull($this->app->view->render('@admin/views/layouts/_angulardirectives'));
        $this->assertNotNull($this->app->view->render('@admin/views/template/default'));
        $this->assertNotNull($this->app->view->render('@admin/views/storage/index'));
        $this->assertNotNull($this->app->view->render('@admin/views/ngrest/_awform'));
        $this->assertNotNull($this->app->view->render('@admin/views/default/dashboard', ['items' => []]));
        $this->assertNotNull($this->app->view->render('@admin/views/default/index'));
    }

    public function testRenderDefault()
    {
        $this->createAdminUserFixture();
        $this->createAdminUserLoginFixture();
        $ctrl = new ControllersDefaultController('default', $this->app->getModule('admin'));

        $r = $ctrl->actionIndex();

        $this->assertNotEmpty($r);
    }
}
