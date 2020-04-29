<?php

namespace luya\admin\tests\admin\apis;

use admintests\AdminModelTestCase;
use luya\admin\apis\MenuController;
use luya\testsuite\scopes\PermissionScope;
use luya\testsuite\traits\AdminDatabaseTableTrait;

class MenuControllerTest extends AdminModelTestCase
{
    use AdminDatabaseTableTrait;

    public function testLoadDataWithoutApiUser()
    {
        
        PermissionScope::run($this->app, function (PermissionScope $scope) {
            $this->app->getModule('admin')->moduleMenus = ['admin' => $this->app->getModule('admin')->getMenu()];
            $this->createAdminLangFixture([]);
            $scope->createAndAllowRoute('admin/id/dashboard');
            $ctrl = new MenuController('id', $this->app->getModule('admin'));
            $response = $scope->runControllerAction($ctrl, 'dashboard', ['nodeId' => 1]);
            $this->assertSame([], $response);

            $this->assertSame([], $ctrl->actionIndex());
            $this->assertSame([], $ctrl->actionItems(1));
        });
    }

    public function testLoadDataWithApiUser()
    {
        
        PermissionScope::run($this->app, function (PermissionScope $scope) {
            $this->app->getModule('admin')->moduleMenus = ['admin' => $this->app->getModule('admin')->getMenu()];
        $this->app->getModule('admin')->dashboardLogDisplayApiUserData = 1;
            $this->createAdminLangFixture([]);
            $scope->createAndAllowRoute('admin/id/dashboard');
            $ctrl = new MenuController('id', $this->app->getModule('admin'));
            $response = $scope->runControllerAction($ctrl, 'dashboard', ['nodeId' => 1]);
            $this->assertSame([], $response);
        });
    }
}
