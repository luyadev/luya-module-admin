<?php

namespace luya\admin\tests\data\controllers;

use luya\rest\Controller;

class TestController extends Controller
{
    public function actionBarFoo()
    {
        return true;
    }
}