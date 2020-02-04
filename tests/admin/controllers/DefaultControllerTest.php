<?php

namespace luya\admin\tests\admin\controllers;

use admintests\AdminModelTestCase;
use luya\admin\controllers\DefaultController;
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
}
