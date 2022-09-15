<?php

namespace admintests\admin\ngrest\actions;

use admintests\AdminModelTestCase;
use luya\admin\apis\UserController;
use luya\testsuite\traits\AdminDatabaseTableTrait;

class ViewActionTest extends AdminModelTestCase
{
    use AdminDatabaseTableTrait;

    public function testViewOptional()
    {
        $this->createAdminLangFixture();

        $userFixture = $this->createAdminUserFixture([
            '1' => [
                'id' => 1,
                'email' => 'john@luya.io',
                'firstname' => 'Test',
                'lastname' => 'Test',
                'is_deleted' => 0,
                'is_api_user' => 0,
            ]
        ]);

        $api = new TestUserApi('id', $this->app);
        $user = $api->runAction('view', ['id' => 1]);
        $this->assertArrayHasKey('id', $user);
    }
}

class TestUserApi extends UserController
{
    public $authOptional = ['view'];
}
