<?php

namespace luya\admin\tests\data\apis;

use luya\admin\apis\UserController;
use luya\admin\components\Auth;

class StubUserApiController extends UserController
{
    public function actionPermissions()
    {
        return [
            'foo-bar' => Auth::CAN_DELETE,
        ];
    }

    public function actionFooBar()
    {
        return 'test!';
    }
}