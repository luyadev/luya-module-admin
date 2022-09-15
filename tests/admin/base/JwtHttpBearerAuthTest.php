<?php

namespace luya\admin\tests\admin\base;

use admintests\AdminTestCase;
use bizley\jwt\JwtHttpBearerAuth;
use luya\helpers\ArrayHelper;

class JwtHttpBearerAuthTest extends AdminTestCase
{
    public function getConfigArray()
    {
        $config = parent::getConfigArray();

        return ArrayHelper::merge($config, [
            'components' => [
                'jwt' => [
                    'class' => 'luya\admin\components\Jwt',
                    'key' => 'Yws49qwwFNJ..F-JERufEbZPntzK4g9EFvYV.Gg!HfTNTbXmGjWtt2odmzM4bhWJuHV8_Aieyp@UKggPYxQ9.4TDxw4qF2kA2W-b',
                    'apiUserEmail' => 'foo@bar.com',
                    'issuer' => 'foobarhost',
                    'audience' => 'luya.io',
                    'identityClass' => [
                        'class' => 'luya\admin\tests\data\models\JwtModel',
                    ],
                ]
            ]
        ]);

        return $config;
    }

    public function testFilter()
    {
        $filter = new JwtHttpBearerAuth();

        $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.'
        . 'eyJzdWIiOiIxMjM0NTY3ODkwIn0.'
        . '2gSBz9EOsQRN9I-3iSxJoFt7NtgV6Rm0IL6a8CAwl3Q';
        $this->assertNull($filter->processToken($token));
    }

    public function testChallange()
    {
        $filter = new JwtHttpBearerAuth();

        $this->assertNull($filter->challenge($this->app->response));
    }

    public function testAuth()
    {
        $filter = new JwtHttpBearerAuth();

        $this->assertNull($filter->authenticate($this->app->adminuser, $this->app->request, $this->app->response));
    }

    public function testHeaderAuth()
    {
        $_SERVER['HTTP_Authorization'] = 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImp0aSI6IjEifQ.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODA4MCIsImF1ZCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDgwIiwianRpIjoiMSIsImlhdCI6MTU2NjQ4MjAxMSwiZXhwIjoxNTY2NDg1NjExLCJ1aWQiOiIxIn0.587xedNWYrOeZeurcJkkG4_S1YPyczFEOE_zBnIuTMo';
        $filter = new JwtHttpBearerAuth();
        $filter->auth = function () {
            return true;
        };
        $this->assertNull($filter->authenticate($this->app->adminuser, $this->app->request, $this->app->response));
    }

    public function testInvalidToken()
    {
        $_SERVER['HTTP_Authorization'] = 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c';
        $filter = new JwtHttpBearerAuth();
        $this->assertNull($filter->authenticate($this->app->adminuser, $this->app->request, $this->app->response));
    }

    public function testUserModelAuth()
    {
        $_SERVER['HTTP_Authorization'] = 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImp0aSI6IjEifQ.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODA4MCIsImF1ZCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDgwIiwianRpIjoiMSIsImlhdCI6MTU2NjQ4MjAxMSwiZXhwIjoxNTY2NDg1NjExLCJ1aWQiOiIxIn0.587xedNWYrOeZeurcJkkG4_S1YPyczFEOE_zBnIuTMo';
        $filter = new JwtHttpBearerAuth();

        $this->assertNull($filter->authenticate($this->app->adminuser, $this->app->request, $this->app->response));
    }
}
