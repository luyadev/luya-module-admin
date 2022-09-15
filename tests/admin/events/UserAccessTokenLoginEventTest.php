<?php

namespace admintests\events;

use admintests\AdminModelTestCase;
use luya\admin\events\UserAccessTokenLoginEvent;
use luya\admin\models\User;
use luya\admin\Module;
use luya\testsuite\traits\AdminDatabaseTableTrait;

class UserAccessTokenLoginEventTest extends AdminModelTestCase
{
    use AdminDatabaseTableTrait;

    public function testLoginUser()
    {
        $this->createAdminUserFixture();

        $user = new User();

        $event = new UserAccessTokenLoginEvent();
        $event->login($user);

        $this->assertSame($user, $event->getUser());

        // test us which is "is_deleted"

        $this->createAdminUserFixture();

        $user = new User();
        $user->is_deleted = true;

        $event = new UserAccessTokenLoginEvent();
        $this->expectException('yii\base\InvalidConfigException');
        $event->login($user);
    }

    public function testLoginByAccessToken()
    {
        $this->createAdminUserFixture();

        $this->app->on(Module::EVENT_USER_ACCESS_TOKEN_LOGIN, function (UserAccessTokenLoginEvent $event) {
            $event->login(new User());
        });


        $this->assertNotEmpty(User::findIdentityByAccessToken('123123'));
    }
}
