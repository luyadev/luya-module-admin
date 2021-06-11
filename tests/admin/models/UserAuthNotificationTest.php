<?php

namespace luya\admin\tests\admin\models;

use admintests\AdminModelTestCase;
use luya\admin\models\Lang;
use luya\admin\models\UserAuthNotification;
use luya\testsuite\fixtures\NgRestModelFixture;

class UserAuthNotificationTest extends AdminModelTestCase
{
    public function testClassNotExistsDiffCount()
    {
        new NgRestModelFixture([
            'modelClass' => UserAuthNotification::class,
        ]);

        $model = new UserAuthNotification();
        $model->model_class = 'does/not/exsists';

        $this->assertSame(0, $model->getDiffCount());

        $this->assertNotNull($model->getAuth());
        $this->assertNotNull($model->getUser());
    }

    public function testExistingDiffCount()
    {
        $fixture = new NgRestModelFixture([
            'modelClass' => UserAuthNotification::class,
        ]);

        new NgRestModelFixture([
            'modelClass' => Lang::class,
            'fixtureData' => [
                1 => [
                    'id' => 1,
                    'is_deleted' => 0,
                ],
                2 => [
                    'id' => 2,
                    'is_deleted' => 0,
                ],
                3 => [
                    'id' => 3,
                    'is_deleted' => 0,
                ]
            ]
        ]);

        /** @var UserAuthNotification $model */
        $model = $fixture->newModel;
        $model->model_class = Lang::class;
        $model->model_latest_pk_value = '["1"]';
        $model->user_id = 1;
        $model->auth_id = 1;
        $model->save();

        $this->assertSame(2, $model->getDiffCount());
    }

    public function testNegativeDiffCOunt()
    {
        $fixture = new NgRestModelFixture([
            'modelClass' => UserAuthNotification::class,
        ]);

        new NgRestModelFixture([
            'modelClass' => Lang::class,
            'fixtureData' => []
        ]);

        /** @var UserAuthNotification $model */
        $model = $fixture->newModel;
        $model->model_class = Lang::class;
        $model->model_latest_pk_value = '["1"]';
        $model->user_id = 1;
        $model->auth_id = 1;
        $model->save();

        // count would be `-1`
        $this->assertSame(0, $model->getDiffCount());
    }
}
