<?php

namespace luya\admin\tests\admin\apis;

use admintests\AdminModelTestCase;
use luya\admin\apis\SearchController;
use luya\admin\apis\TimestampController;
use luya\admin\models\SearchData;
use luya\testsuite\fixtures\NgRestModelFixture;
use luya\testsuite\scopes\PermissionScope;

class SearchControllerTest extends AdminModelTestCase
{
    public function testIndexAction()
    {
        PermissionScope::run($this->app, function (PermissionScope $scope) {
            $fixture = new NgRestModelFixture([
                'modelClass' => SearchData::class,
            ]);
            $ctrl = new SearchController('timestamp', $scope->getApp()->getModule('admin'));
            $scope->loginUser();
            $response = $scope->runControllerAction($ctrl, 'index', ['query' => 'test']);

            $this->assertEmpty($response);
        }, function (PermissionScope $config) {
            $config->userFixtureData = ['is_api_user' => 0];
        });
    }
}
