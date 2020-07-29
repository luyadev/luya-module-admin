<?php

namespace admintests\admin\traits;

use admintests\AdminModelTestCase;
use luya\admin\models\User;

class SoftDeleteTraitTest extends AdminModelTestCase
{
    public function testFindWithRelation()
    {
        $groupFixture = $this->createAdminGroupFixture(1);
        $userFixture = $this->createAdminUserFixture([
            [
                'id' => 1,
                'firstname' => 'John',
                'lastname' => 'Doe',
                'email' => 'john@example.com',
                'is_deleted' => 1,
            ]
        ]);
        $userFixture = $this->createAdminUserFixture([
            [
                'id' => 2,
                'firstname' => 'John',
                'lastname' => 'Doe 2',
                'email' => 'doe@example.com',
                'is_deleted' => 0,
            ]
        ]);
        $this->createAdminUserGroupTable();
        $userGroupId = $this->insertRow('admin_user_group', [
            'user_id' => 2,
            'group_id' => 1,
        ]);
        
        $userQuery = User::find()->joinWith('groups')->indexBy('id')->prepare(null);
        /** @var User $user */
        $user = $userQuery->one();
        
        $this->assertEquals([
            'and',
            ['is_deleted' => false],
            ['{{%admin_group}}.is_deleted' => false],
        ], $userQuery->where);
        
        $this->assertEquals('doe@example.com', $user['email']);
        $this->assertEquals('Test Group', $user->groups[0]->name);
    }
}
