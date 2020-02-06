<?php

namespace admintests\admin\ngrest\actions;

use admintests\AdminModelTestCase;
use luya\admin\apis\UserController;
use luya\admin\models\User;
use luya\admin\ngrest\base\actions\DeleteAction;
use luya\testsuite\fixtures\NgRestModelFixture;

class DeleteActionTest extends AdminModelTestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testDeleteObjectNotFound()
    {
        $user = new NgRestModelFixture([
            'modelClass' => User::class,
        ]);

        $ctrl = new UserController('user', $this->app);
        $delete = new DeleteAction('id', $ctrl, ['modelClass' => User::class]);

        $this->expectException('yii\web\NotFoundHttpException');
        $run = $delete->run(2);
    }
}
