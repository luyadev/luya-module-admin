<?php

namespace luya\admin\tests\admin\aws;

use admintests\AdminModelTestCase;
use luya\admin\aws\ApiRequestInsightActiveWindow;
use luya\admin\models\User;
use luya\admin\models\UserRequest;
use luya\testsuite\fixtures\NgRestModelFixture;
use yii\data\ActiveDataProvider;

class ApiRequestInsightActiveWindowTest extends AdminModelTestCase
{
    public function testRender()
    {
        new NgRestModelFixture([
            'modelClass' => UserRequest::class,
        ]);


        new NgRestModelFixture([
            'modelClass' => User::class,
            'fixtureData' => [
                1 => [
                    'id' => 1,
                    'is_deleted' => 0,
                    'is_api_user' => 0,
                    'firstname' => 'john',
                    'lastname' => 'doe',
                    'email' => 'test@luya.io',
                    'is_request_logger_enabled' => 1,
                ]
            ]
        ]);
        $aw = new ApiRequestInsightActiveWindow([
            'ngRestModelClass' => User::class,
            'itemId' => 1
        ]);

        $this->assertNotEmpty($aw->index());

        $this->assertInstanceOf(ActiveDataProvider::class, $aw->callbackData());
    }
}
