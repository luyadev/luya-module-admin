<?php

namespace admintests\admin\traits;

use admintests\AdminModelTestCase;
use admintests\AdminTestCase;
use admintests\data\fixtures\UserFixture;
use luya\admin\models\User;
use luya\admin\models\UserGroup;

class SoftDeleteTraitTest extends AdminTestCase
{
    public function testFindWithRelation()
    {
        $model = new UserFixture();
        $model->load();
    
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
}
