<?php

namespace luya\admin\tests\admin\apis;

use admintests\AdminModelTestCase;
use luya\admin\apis\NgrestLogController;
use luya\admin\models\NgrestLog;
use luya\testsuite\scopes\PermissionScope;
use yii\base\InvalidConfigException;

class NgrestLogControllerTest extends AdminModelTestCase
{
    public function testInvalidType()
    {
        $this->createAdminLangFixture();
        $this->createAdminNgRestLogFixture();

        $api = new NgrestLogController('log', $this->app->getModule('admin'));

        $this->expectException(InvalidConfigException::class);
        $api->actionExport();
    }

    public function testExportAndFormatter()
    {
        PermissionScope::run($this->app, function (PermissionScope $scope) {
            $scope->createAndAllowApi('api-admin-ngrestlog');
            $this->app->request->setBodyParams(['type' => 'csv']);

            $api = new NgrestLogController('api-admin-ngrestlog', $this->app->getModule('admin'));

            $this->assertArrayHasKey('url', $scope->runControllerAction($api, 'export'));

            $log = new NgrestLog();
            $log->attributes = [
                'id' => 1,
                'user_id' => 1,
                'timestamp_create' => 123123123,
                'route' => 'foo',
                'api' => 'foo',
                'is_update' => 1,
                'is_insert' => 0,
                'attributes_json' => '{}',
                'attributes_diff_json' => '{}',
                'pk_value' => "1",
                'table_name' => 'foo',
                'is_delete' => 1,
            ];

            $this->assertTrue($log->save());

            $export = $this->invokeMethod($api, 'formatExportValues', [NgrestLog::find(), [
                'is_update' => 'boolean',
                'table_name' => function ($model) {
                    return md5($model->table_name);
                }
            ]]);

            $this->assertSame('acbd18db4cc2f85cedef654fccc4a4d8', $export[0][$log->getAttributeLabel('table_name')]);
            $this->assertSame('Yes', $export[0][$log->getAttributeLabel('is_update')]);
        });
    }
}
