<?php

namespace luya\admin\tests\admin\base;

use admintests\AdminTestCase;
use luya\helpers\ArrayHelper;
use sizeg\jwt\JwtHttpBearerAuth;

class JwtHttpBearerAuthTest extends AdminTestCase
{
    public function getConfigArray()
    {
        $config = parent::getConfigArray();

        return ArrayHelper::merge($config, [
            'components' => [
                'jwt' => [
                    'class' => 'luya\admin\components\Jwt',
                    'key' => '3jlsdkfjlsdkjfsldjf',
                    'apiUserEmail' => 'foo@bar.com',
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

        $this->assertNull($filter->loadToken('abc'));
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
        $_SERVER['HTTP_Authorization'] = 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImp0aSI6IjEifQ';
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
