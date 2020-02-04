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
}
