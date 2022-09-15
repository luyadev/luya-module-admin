<?php

namespace luya\admin\tests\admin\base;

use admintests\AdminModelTestCase;
use luya\admin\base\RestActiveController;

class RestActiveControllerTest extends AdminModelTestCase
{
    public function testCheckEndpointAccess()
    {
        $_SERVER['REQUEST_METHOD'] = 'OPTIONS';
        $this->createAdminLangFixture();
        $sub = new class ('id', $this->app) extends RestActiveController {
            public $modelClass = 'User';
        };
        $sub->enableCors = true;
        $this->assertTrue($sub->isActionAuthOptional('unknown'));
    }
}
