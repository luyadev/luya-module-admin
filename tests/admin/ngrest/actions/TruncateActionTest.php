<?php

namespace admintests\admin\ngrest\actions;

use admintests\AdminModelTestCase;
use luya\admin\apis\UserController;
use luya\admin\models\User;
use luya\admin\ngrest\base\actions\TruncateAction;
use luya\testsuite\fixtures\NgRestModelFixture;

class TruncateActionTest extends AdminModelTestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testDeleteResponse204()
    {
        $user = new NgRestModelFixture([
            'modelClass' => User::class,
        ]);

        $this->createAdminLangFixture([]);

        $ctrl = new UserController('user', $this->app);
        $delete = new TruncateAction('id', $ctrl, ['modelClass' => User::class]);

        $this->assertNull($delete->run());
    }
}
