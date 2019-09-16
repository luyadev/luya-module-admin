<?php

namespace luya\admin\tests\admin\models;

use admintests\AdminModelTestCase;
use luya\admin\models\LoginForm;
use luya\admin\models\User;
use luya\admin\Module;
use luya\testsuite\traits\AdminDatabaseTableTrait;

class LoginFormTest extends AdminModelTestCase
{
    use AdminDatabaseTableTrait;

    public function testSendSecureToken()
    {
        $user = $this->createUserFixture([
            'user' => [
                'id' => 1,
                'firstname' => 'John',
                'lastname' => 'Doe',
                'email' => 'test@luya.io',
                'is_deleted' => 0,
                'is_api_user' => 0,
            ]
        ]);

        $this->createNgRestLogFixture();

        $login = new LoginForm();
        $login->email = 'test@luya.io';
        $this->assertFalse($login->sendSecureLogin());
        
        $token = 'testtoken';
        $emailBody = User::generateTokenEmail($token, Module::t('login_securetoken_mail_subject'), Module::t('login_securetoken_mail'));

        $this->assertContains($token, $emailBody);
        $this->assertContains(Module::t('login_securetoken_mail_subject'), $emailBody);
        $this->assertContains(Module::t('login_securetoken_mail'), $emailBody);
    }
}