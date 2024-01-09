<?php

namespace luya\admin\tests\admin\aws;

use admintests\AdminModelTestCase;
use luya\admin\aws\UserHistorySummaryActiveWindow;
use luya\admin\models\User;
use luya\testsuite\traits\AdminDatabaseTableTrait;

class UserHistorySummaryActiveWindowTest extends AdminModelTestCase
{
    use AdminDatabaseTableTrait;

    public function testRenderWithLogs()
    {
        $currentLocale = setlocale(LC_TIME, 0);

        setlocale(LC_TIME, 'C');

        $userFixture = $this->createAdminUserFixture([
            1  => [
                'id' => 1,
                'firstname' => 'Foo',
                'lastname' => 'Bar',
                'email' => 'foo@example.com',
                'is_deleted' => false,
                'is_api_user' => false,
                'api_last_activity' => 12345678,
            ]
        ]);

        $userGroup = $this->createAdminGroupFixture(1);
        $userGroupUser = $this->createAdminUserGroupTable();
        $this->createAdminUserLoginFixture();
        $this->createAdminNgRestLogFixture([
            1 => [
                'id' => 1,
                'user_id' => 1,
                'timestamp_create' => 123123,
                'is_insert' => 0,
                'is_update' => 1,
                'attributes_json' => '{}',
                'attributes_diff_json' => '{}',
            ],
            2 => [
                'id' => 2,
                'user_id' => 1,
                'timestamp_create' => 123123,
                'is_insert' => 0,
                'is_update' => 1,
                'attributes_json' => '{"foo":"baz"}',
                'attributes_diff_json' => '{"foo":"bar"}',
            ],
            3 => [
                'id' => 3,
                'user_id' => 1,
                'timestamp_create' => 123123,
                'is_insert' => 1,
                'is_update' => 0,
                'attributes_json' => '{"foo":"baz"}',
                'attributes_diff_json' => '{"foo":"bar"}',
            ],
            4 => [
                'id' => 4,
                'user_id' => 1,
                'timestamp_create' => 123123,
                'is_insert' => 0,
                'is_update' => 1,
                'attributes_json' => '{"foo":"bar"}',
                'attributes_diff_json' => '{"foo":"bar"}',
            ],
        ]);

        $aws = new UserHistorySummaryActiveWindow();
        $aws->ngRestModelClass = User::class;
        $aws->setItemId(1);

        $expect = '<script>zaa.bootstrap.register(\'UserHistorySummaryController\', [\'$scope\', function($scope) { $scope.pie = false; $scope.loadPieCharts = function() { $scope.$parent.sendActiveWindowCallback(\'pie\').then(function(response) { $scope.pie = response.data; }); }; $scope.loadPieCharts(); }]);</script><div class="row" ng-controller="UserHistorySummaryController"><div class="col-lg-3"><div class="card mb-4"><div class="card-header">Profile details</div><div class="table-responsive"><table class="table table-sm table-borderless table-striped small"><tr><td>Title</td><td>1</td></tr><tr><td>Name</td><td>Foo Bar</td></tr><tr><td>Email</td><td><a href="mailto:foo@example.com">foo@example.com</a></td></tr><tr><td>Removed</td><td>No</td></tr><tr><td>Groups</td><td></td></tr><tr><td>Last API activity</td><td>5/23/70, 9:21 PM</td></tr></table></div></div><div class="card mb-4"><div class="card-header">Total contributions</div><div class="card-body" ng-if="pie"><echarts id="userEchart" data="pie"></echarts></div></div><div class="card"><div class="card-header">Custom settings</div><div class="table-responsive"><table class="table table-sm table-borderless table-striped small"></table></div></div></div><div class="col-lg-3"><div class="card"><div class="card-header">Latest sessions</div><table class="table table-sm table-striped table-borderless table-hoverable small mb-0"></table></div></div><div class="col-lg-6"><div class="card"><div class="card-header">Change history</div><ul class="list-group list-group-flush"><li class="list-group-item d-flex justify-content-between align-items-center p-2"><span><i class="material-icons" alt="Updated">create</i><span class="badge badge-secondary"></span><span class="badge badge-info">ID #</span>54 years ago</span><span class="badge badge-primary badge-pill" ng-click="hiddenElement1=!hiddenElement1">Diff</span></li><li class="list-group-item p-0" ng-show="hiddenElement1"><div class="table-responsive"><table class="table table-sm table-striped table-borderless table-hoverable small"><thead><tr><th class="w-25">Attribute</th><th class="w-25">Old</th><th class="w-50">New</th></tr></thead><tr><td colspan="3">No changes</td></tr></table></div></li><li class="list-group-item d-flex justify-content-between align-items-center p-2"><span><i class="material-icons" alt="Updated">create</i><span class="badge badge-secondary"></span><span class="badge badge-info">ID #</span>54 years ago</span><span class="badge badge-primary badge-pill" ng-click="hiddenElement2=!hiddenElement2">Diff</span></li><li class="list-group-item p-0" ng-show="hiddenElement2"><div class="table-responsive"><table class="table table-sm table-striped table-borderless table-hoverable small"><thead><tr><th class="w-25">Attribute</th><th class="w-25">Old</th><th class="w-50">New</th></tr></thead><tr><td>foo</td><td>bar</td><td>baz</td></tr></table></div></li><li class="list-group-item d-flex justify-content-between align-items-center p-2"><span><i class="material-icons" alt="Added">add_box</i><span class="badge badge-secondary"></span><span class="badge badge-info">ID #</span>54 years ago</span><span class="badge badge-primary badge-pill" ng-click="hiddenElement3=!hiddenElement3">Diff</span></li><li class="list-group-item p-0" ng-show="hiddenElement3"><div class="table-responsive"><table class="table table-sm table-striped table-borderless table-hoverable small"><thead><tr><th class="w-25">Attribute</th><th class="w-25">Old</th><th class="w-50">New</th></tr></thead><tr><td>foo</td><td>-</td><td>baz</td></tr></table></div></li><li class="list-group-item d-flex justify-content-between align-items-center p-2"><span><i class="material-icons" alt="Updated">create</i><span class="badge badge-secondary"></span><span class="badge badge-info">ID #</span>54 years ago</span><span class="badge badge-primary badge-pill" ng-click="hiddenElement4=!hiddenElement4">Diff</span></li><li class="list-group-item p-0" ng-show="hiddenElement4"><div class="table-responsive"><table class="table table-sm table-striped table-borderless table-hoverable small"><thead><tr><th class="w-25">Attribute</th><th class="w-25">Old</th><th class="w-50">New</th></tr></thead><tr><td colspan="3">No changes</td></tr></table></div></li></ul></div></div></div>';

        $this->assertContainsTrimmed($expect, $aws->index());

        setlocale(LC_TIME, $currentLocale);
    }
}
