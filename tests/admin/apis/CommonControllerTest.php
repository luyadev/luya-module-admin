<?php

namespace luya\admin\tests\admin\apis;

use admintests\AdminModelTestCase;
use luya\admin\apis\CommonController;
use luya\admin\models\Tag;
use luya\admin\models\TagRelation;
use luya\testsuite\fixtures\ActiveRecordFixture;
use luya\testsuite\fixtures\NgRestModelFixture;
use luya\testsuite\scopes\PermissionScope;
use luya\testsuite\traits\AdminDatabaseTableTrait;

class CommonControllerTest extends AdminModelTestCase
{
    use AdminDatabaseTableTrait;

    public function testActionTagRelationToggle()
    {
        PermissionScope::run($this->app, function(PermissionScope $scope) {

            $this->createAdminLangFixture([]);

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
        });
    }
}