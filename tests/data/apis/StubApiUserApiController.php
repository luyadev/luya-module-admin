<?php

namespace luya\admin\tests\data\apis;

use luya\admin\apis\ApiUserController;
use luya\admin\components\Auth;

class StubApiUserApiController extends ApiUserController
{
    public function actionPermissions()
    {
        return [
            'foo-bar' => Auth::CAN_DELETE,
            'invalid' => 'invalid',
        ];
    }

    public function actionFooBar()
    {
        return 'test!';
    }

    public function actionInvalid()
    {
        return 'invalid';
    }

    public function actionVisible()
    {
        return 'visible';
    }
}
