<?php

namespace luya\admin\tests\admin\apis;

use admintests\AdminModelTestCase;
use luya\admin\apis\CommonController;
use luya\admin\models\Property;
use luya\admin\models\Tag;
use luya\admin\models\TagRelation;
use luya\admin\tests\data\properties\MyTestProperty;
use luya\testsuite\fixtures\ActiveRecordFixture;
use luya\testsuite\fixtures\NgRestModelFixture;
use luya\testsuite\scopes\PermissionScope;
use luya\testsuite\traits\AdminDatabaseTableTrait;

class CommonControllerTest extends AdminModelTestCase
{
    use AdminDatabaseTableTrait;

    /**
     * @runInSeparateProcess
     */
    public function testActionTagRelationToggle()
    {
        PermissionScope::run($this->app, function (PermissionScope $scope) {
            $lang = $this->createAdminLangFixture([]);

            $tag = new NgRestModelFixture([
                'modelClass' => Tag::class,
                'fixtureData' => [
                    'tag1' => [
                        'id' => 1,
                        'name' => '#barfoo'
                    ]
                ]
            ]);

            $rel = new ActiveRecordFixture([
                'modelClass' => TagRelation::class,
            ]);

            $scope->createAndAllowRoute('adminmodeltest/id/tag-relation-toggle');

            $ctrl = new CommonController('id', $this->app);

            $response = $scope->runControllerAction($ctrl, 'tag-relation-toggle', [
                'tagId' => 1,
                'pkId' => 2,
                'tableName' => 'test',
            ]);

            $this->assertTrue($response);

            $this->expectException('yii\base\InvalidCallException');
            $response = $scope->runControllerAction($ctrl, 'tag-relation-toggle', [
                'tagId' => 100,
                'pkId' => 2,
                'tableName' => 'test',
            ]);

            $tag->cleanup();
            $rel->cleanup();
            $lang->cleanup();
        });
    }

    /**
     * @runInSeparateProcess
     */
    public function testActionTags()
    {
        PermissionScope::run($this->app, function (PermissionScope $scope) {
            $this->createAdminLangFixture([]);
            $scope->createAndAllowRoute('adminmodeltest/id/tags');
            $ctrl = new CommonController('id', $this->app);
            $tag = new NgRestModelFixture([
                'modelClass' => Tag::class,
                'fixtureData' => [
                ]
            ]);
            $response = $scope->runControllerAction($ctrl, 'tags');

            $this->assertSame([], $response);
        });
    }

    /**
     * @runInSeparateProcess
     */
    public function testActionQueueJob()
    {
        $this->createAdminLangFixture();
        $this->createAdminQueueTable();
        $ctrl = new CommonController('id', $this->app->getModule('admin'));

        $r = $ctrl->actionQueueJob(0);

        $this->assertArrayHasKey('is_waiting', $r);
        $this->assertArrayHasKey('is_reserved', $r);
        $this->assertArrayHasKey('is_done', $r);
    }

    public function testDataProperties()
    {
        PermissionScope::run($this->app, function (PermissionScope $scope) {
            $this->createAdminLangFixture([]);
            new NgRestModelFixture([
                'modelClass' => Property::class,
                'fixtureData' => [
                    1 => [
                        'id' => 1,
                        'module_name' => 'admin',
                        'var_name' => 'foobar',
                        'class_name' => MyTestProperty::class,
                        'is_deleted' => 0
                    ]
                ],
            ]);
            $scope->createAndAllowRoute('adminmodeltest/id/data-properties');
            $ctrl = new CommonController('id', $this->app);
            $tag = new NgRestModelFixture([
                'modelClass' => Tag::class,
                'fixtureData' => [
                ]
            ]);
            $response = $scope->runControllerAction($ctrl, 'data-properties');

            $this->assertSame([
                [
                    'id' => 1,
                    'var_name' => 'barfoo',
                    'option_json' => [],
                    'label' => 'Label',
                    'type' => 'zaa-text',
                    'default_value' => false,
                    'help' => null,
                    'i18n' => false,
                ]
            ], $response);
        });
    }
}
