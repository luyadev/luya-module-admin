<?php

namespace luya\admin\tests\admin\apis;

use admintests\AdminModelTestCase;
use luya\admin\apis\MenuController;
use luya\testsuite\traits\AdminDatabaseTableTrait;

class MenuControllerTest extends AdminModelTestCase
{
    use AdminDatabaseTableTrait;

    /**
     * @runInSeparateProcess
     */
    public function testLoadDataWithoutApiUser()
    {
        $this->createAdminLangFixture();
        $this->createAdminNgRestLogFixture();
        $this->createAdminUserFixture();
        $this->createAdminUserGroupTable();
        $this->createAdminAuthTable();
        $this->createAdminGroupAuthTable();
        $this->createAdminUserOnlineFixture();
        $ctrl = new MenuController('id', $this->app->getModule('admin'));
        $this->assertSame([], $ctrl->actionDashboard(1));
        $this->assertSame([], $ctrl->actionIndex());
        $this->assertSame([], $ctrl->actionItems(1));
    }

    /**
     * @runInSeparateProcess
     */
    public function testLoadDataWithApiUser()
    {
        $this->createAdminLangFixture();
        $this->createAdminNgRestLogFixture();
        $this->createAdminUserFixture();
        $this->createAdminUserGroupTable();
        $this->createAdminAuthTable();
        $this->createAdminGroupAuthTable();
        $this->createAdminUserOnlineFixture();
        $this->app->getModule('admin')->dashboardLogDisplayApiUserData = 1;
        $ctrl = new MenuController('id', $this->app->getModule('admin'));
        $this->assertSame([], $ctrl->actionDashboard(1));
        $this->assertSame([], $ctrl->actionIndex());
        $this->assertSame([], $ctrl->actionItems(1));
    }
}
