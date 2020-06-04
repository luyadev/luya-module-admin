<?php

namespace luya\admin\tests\admin\aws;

use admintests\AdminModelTestCase;
use luya\admin\aws\UserHistorySummaryActiveWindow;
use luya\admin\models\User;
use luya\testsuite\traits\AdminDatabaseTableTrait;

class UserHistorySummaryActiveWindowTest extends AdminModelTestCase
{
    use AdminDatabaseTableTrait;

    public function testRenderWithLogs()
    {
        $userFixture = $this->createAdminUserFixture([
            1  => [
                'id' => 1,
                'firstname' => 'Foo',
                'lastname' => 'Bar',
                'email' => 'foo@example.com',
                'is_deleted' => false,
                'is_api_user' => false,
            ]
        ]);

        $userGroup = $this->createAdminGroupFixture(1);
        $userGroupUser = $this->createAdminUserGroupTable();
        $this->createAdminUserLoginFixture();
        $this->createAdminNgRestLogFixture([
            1 => [
                'id' => 1,
                'user_id' => 1,
                'timestamp_create' => time(),
                'is_insert' => 0,
                'is_update' => 1,
                'attributes_json' => '{}',
                'attributes_diff_json' => '{}',
            ],
            2 => [
                'id' => 2,
                'user_id' => 1,
                'timestamp_create' => time(),
                'is_insert' => 0,
                'is_update' => 1,
                'attributes_json' => '{"foo":"baz"}',
                'attributes_diff_json' => '{"foo":"bar"}',
            ],
            3 => [
                'id' => 3,
                'user_id' => 1,
                'timestamp_create' => time(),
                'is_insert' => 1,
                'is_update' => 0,
                'attributes_json' => '{"foo":"baz"}',
                'attributes_diff_json' => '{"foo":"bar"}',
            ],
        ]);

        $aws = new UserHistorySummaryActiveWindow();
        $aws->ngRestModelClass = User::class;
        $aws->setItemId(1);

        $this->assertSame('ff', $aws->index());
    }
}