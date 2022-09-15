<?php

namespace admintests\models;

use admintests\AdminModelTestCase;
use luya\admin\models\UserDevice;
use luya\testsuite\fixtures\NgRestModelFixture;
use luya\testsuite\traits\AdminDatabaseTableTrait;

class UserModelTest extends AdminModelTestCase
{
    use AdminDatabaseTableTrait;

    public function testUser()
    {
        $user = $this->createAdminUserFixture();

        $this->assertSame(false, $user->newModel->getAuthKey());

        $this->app->request->headers->add('User-Agent', 'barfoo');
        $devices = new NgRestModelFixture(['modelClass' => UserDevice::class]);
        $this->assertSame(false, $user->newModel->getAuthKey());

        $this->assertFalse($user->newModel->validateAuthKey('bar'));
    }

    public function testDeletedUserExistsValidator()
    {
        $this->createAdminNgRestLogFixture();
        $user = $this->createAdminUserFixture();

        $user1 = $user->newModel;
        $user1->title = 1;
        $user1->password = 'ABCdef123!@';
        $user1->email = 'delete@luya.io';
        $user1->firstname = 'delete';
        $user1->lastname = 'delete';
        $user1->is_deleted = 1;

        $this->assertTrue($user1->save());

        $user2 = $user->newModel;
        $user2->title = 1;
        $user2->password = 'ABCdef123!@';
        $user2->email = 'delete@luya.io';
        $user2->firstname = 'delete';
        $user2->lastname = 'delete';

        $this->assertFalse($user2->save());
        $this->assertSame('The provided email address is already in use by a deleted account.', $user2->getFirstError('email'));
    }
}
