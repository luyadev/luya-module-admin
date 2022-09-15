<?php

namespace admintests\admin\traits;

use admintests\AdminModelTestCase;
use luya\admin\models\Group;
use luya\admin\models\User;
use luya\admin\models\UserOnline;
use luya\testsuite\fixtures\NgRestModelFixture;

class SoftDeleteTraitTest extends AdminModelTestCase
{
    public function testFindWithRelation()
    {
        new NgRestModelFixture([
            'modelClass' => User::class,
            'fixtureData' => [
                'user1' => [
                    'id' => 1,
                    'title' => 1,
                    'firstname' => 'John',
                    'lastname' => 'Doe',
                    'email' => 'john@luya.io',
                    'password' => 'nohash',
                    'is_deleted' => 0,
                    'is_api_user' => 0,
                ],
                'user2' => [
                    'id' => 2,
                    'title' => 2,
                    'firstname' => 'Jane',
                    'lastname' => 'Doe',
                    'email' => 'jane@luya.io',
                    'password' => 'nohash',
                    'is_deleted' => 0,
                    'is_api_user' => 0,
                ],
                'user3' => [
                    'id' => 3,
                    'title' => 3,
                    'firstname' => 'Is',
                    'lastname' => 'Deleted',
                    'email' => 'deleted@luya.io',
                    'password' => 'nohash',
                    'is_deleted' => 1,
                    'is_api_user' => 0,
                ]
            ]
        ]);


        $this->createAdminUserGroupTable();

        $groupFixture = new NgRestModelFixture([
            'modelClass' => Group::class,
            'fixtureData' => [
                'tester' => [
                    'id' => 1,
                    'name' => 'Administrator',
                    'is_deleted' => 0,
                ],
            ],
        ]);

        $this->app->db->createCommand()->truncateTable('admin_user_group')->execute();
        $this->app->db->createCommand()->insert('admin_user_group', [
            'user_id' => 2,
            'group_id' => 1,
        ])->execute();
        $this->app->db->createCommand()->insert('admin_user_group', [
            'user_id' => 3,
            'group_id' => 1,
        ])->execute();

        $userQuery = User::find()->indexBy('id');
        $this->assertEquals(['{{%admin_user}}.is_deleted' => false], $userQuery->prepare(null)->where);

        $users = $userQuery->all();
        $this->assertCount(2, $users);
        $this->assertEquals('john@luya.io', $users[1]->email);
        $this->assertEquals('jane@luya.io', $users[2]->email);

        $userQuery = User::find()->indexBy('id')->innerJoinWith('groups');
        $this->assertEquals([
            'and',
            ['{{%admin_user}}.is_deleted' => false],
            ['{{%admin_group}}.is_deleted' => false],
        ], $userQuery->prepare(null)->where);

        $users = $userQuery->all();
        $this->assertCount(1, $users);
        $this->assertEquals('jane@luya.io', $users[2]->email);
        $this->assertEquals('Administrator', $users[2]->groups[0]->name);
    }

    public function testAliasTableNamesInSoftDeleteWhereCondition()
    {
        $this->createAdminUserOnlineFixture();
        $this->createAdminUserFixture();

        // will faile
        $query = UserOnline::find()
            ->select(['lock_pk', 'lock_table', 'last_timestamp', 'firstname', 'lastname', 'admin_user.id'])
            ->where(['!=', 'admin_user.id', 1])
            ->joinWith('user')
            ->asArray()
            ->all();

        $this->assertSame(0, count($query));
    }
}
