<?php

namespace luya\admin\tests\admin\base;

use admintests\AdminTestCase;
use luya\admin\base\RestActiveController;

class RestActiveControllerTest extends AdminTestCase
{
    public function testCheckEndpointAccess()
    {
        $ctrl = new RestActiveController('id', $this->app, [
            'modelClass' => 'unknown',
        ]);

        $this->expectException('yii\web\ForbiddenHttpException');
        $ctrl->checkEndpointAccess();
    }
}