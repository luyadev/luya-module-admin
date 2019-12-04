<?php

namespace luya\admin\tests\data\controllers;

use luya\admin\base\RestController;

class TestController extends RestController
{
    public $authOptional = ['no-auth'];

    public function actionNoAuth()
    {
        return true;
    }

    public function actionBarFoo()
    {
        return true;
    }
}
