<?php

namespace tests\admin\aws;

use admintests\AdminModelTestCase;
use luya\admin\aws\DeleteTagsActiveWindow;
use luya\admin\models\Tag;
use luya\admin\models\TagRelation;
use luya\testsuite\fixtures\NgRestModelFixture;
use luya\testsuite\traits\AdminDatabaseTableTrait;

class DeleteTagsActiveWindowTest extends AdminModelTestCase
{
    use AdminDatabaseTableTrait;

    public function afterSetup()
    {
        parent::afterSetup();

        $this->langFixture = $this->createAdminLangFixture([]);

        $this->tagFixture = new NgRestModelFixture([
            'modelClass' => Tag::class,
            'fixtureData' => [
                1 => [
                    'id' => 1,
                    'name' => 'foobar',
                ]
            ]
        ]);

        $this->tagRelationFixture = new NgRestModelFixture([
            'modelClass' => TagRelation::class,
            'fixtureData' => [
                1 => [
                    'pk_id' => 1,
                    'table_name' => 'test1',
                    'tag_id' => 1,
                ],
                2 => [
                    'pk_id' => 2,
                    'table_name' => 'test1',
                    'tag_id' => 1,
                ],
                3 => [
                    'pk_id' => 1,
                    'table_name' => 'test2',
                    'tag_id' => 1,
                ],
            ]
        ]);
    }


    public function testRender()
    {
        $tagModel = $this->tagFixture->getData(1);

        $aws = new DeleteTagsActiveWindow();
        $aws->ngRestModelClass = Tag::class;
        $aws->itemId = 1;

        $html = 'ff';
        $this->assertSame($html, $aws->index());
    }

    public function beforeTearDown()
    {
        $this->tagFixtures->cleanup();
        $this->tagRelationFixture->cleanup();
        $this->langFixture->cleanup();
    }
}