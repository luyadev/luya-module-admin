<?php

namespace luya\admin\tests\data\controllers;

use luya\admin\controllers\ApiUserController;

class StubApiUserControllerController extends ApiUserController
{
    public function actionFooBar()
    {
        return $this->module->id . '/' . $this->id . '/' . 'foo-bar';
    }
}
