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
    public function testDeleteObjectNotFound()
    {
        $user = new NgRestModelFixture([
            'modelClass' => User::class,
        ]);

        $ctrl = new UserController('user', $this->app);
        $delete = new TruncateAction('id', $ctrl, ['modelClass' => User::class]);

        $this->assertNull($delete->run());
        
    }
}
