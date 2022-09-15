<?php

namespace admintests\models;

use admintests\AdminTestCase;
use admintests\data\fixtures\UserFixture;
use luya\admin\models\User;

class UserTest extends AdminTestCase
{
    public function testUser()
    {
        $model = new UserFixture();
        $model->load();
        $user = $model->getModel('user1');

        $this->assertInstanceOf('luya\admin\models\User', $user);

        $user->firstname = '<script>alert(0)</script>';
        $user->update(true, ['firstname']);

        $this->assertSame('&lt;script&gt;alert(0)&lt;/script&gt;', $user->firstname);
    }

    public function testFindByEmail()
    {
        $model = new UserFixture();
        $model->load();
        $user = User::findByEmail('jane@luya.io');
        $this->assertSame('Jane', $user->firstname);

        $user = User::findByEmail('deleted@luya.io');
        $this->assertNull($user);
    }
}
