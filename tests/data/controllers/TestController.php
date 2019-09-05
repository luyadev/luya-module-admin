<?php

namespace luya\admin\tests\data\controllers;

use luya\admin\base\RestController;

class TestController extends RestController
{
    public function actionBarFoo()
    {
        return true;
    }
}