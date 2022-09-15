<?php

namespace admintests\admin\ngrest\actions;

use admintests\AdminModelTestCase;
use luya\admin\apis\UserController;
use luya\admin\models\User;
use luya\admin\ngrest\base\actions\DeleteAction;
use luya\testsuite\fixtures\NgRestModelFixture;
use luya\testsuite\traits\AdminDatabaseTableTrait;

class DeleteActionTest extends AdminModelTestCase
{
    use AdminDatabaseTableTrait;

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testDeleteObjectNotFound()
    {
        $this->createAdminLangFixture();

        $user = new NgRestModelFixture([
            'modelClass' => User::class,
        ]);

        $ctrl = new UserController('user', $this->app);
        $delete = new DeleteAction('id', $ctrl, ['modelClass' => User::class]);

        $this->expectException('yii\web\NotFoundHttpException');
        $run = $delete->run(2);
    }
}
