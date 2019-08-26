<?php

namespace luya\admin\tests\admin\base;

use admintests\AdminTestCase;
use sizeg\jwt\JwtHttpBearerAuth;

class JwtHttpBearerAuthTest extends AdminTestCase
{
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
}