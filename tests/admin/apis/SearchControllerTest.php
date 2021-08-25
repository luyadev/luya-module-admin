<?php

namespace luya\admin\tests\admin\apis;

use admintests\AdminModelTestCase;
use luya\admin\apis\SearchController;
use luya\admin\models\SearchData;
use luya\testsuite\fixtures\NgRestModelFixture;
use luya\testsuite\scopes\PermissionScope;

class SearchControllerTest extends AdminModelTestCase
{
    public function testIndexAction()
    {
        PermissionScope::run($this->app, function (PermissionScope $scope) {
            new NgRestModelFixture([
                'modelClass' => SearchData::class,
            ]);

            $this->createAdminTagFixture([
                1 => [
                    'id' => 1,
                    'name' => 'none',
                    'translation' => 'none',
                ]
            ]);

            $ctrl = new SearchController('search', $scope->getApp()->getModule('admin'));
            $scope->loginUser();
            $response = $scope->runControllerAction($ctrl, 'index', ['query' => 'test']);

            $this->assertEmpty($response);
        }, function (PermissionScope $config) {
            $config->userFixtureData = ['is_api_user' => 0];
        });
    }
}
