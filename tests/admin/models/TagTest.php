<?php

namespace luya\admin\tests\admin\models;

use admintests\AdminModelTestCase;
use luya\admin\models\Tag;
use luya\admin\models\TagRelation;
use luya\admin\models\UserOnline;
use luya\testsuite\fixtures\ActiveRecordFixture;
use luya\testsuite\fixtures\NgRestModelFixture;
use luya\testsuite\traits\AdminDatabaseTableTrait;

class TagTest extends AdminModelTestCase
{
    use AdminDatabaseTableTrait;

    public function testToggleModel()
    {
        $this->createNgRestLogFixture();

        $tag = new NgRestModelFixture([
            'modelClass' => Tag::class,
        ]);

        $rel = new ActiveRecordFixture([
            'modelClass' => TagRelation::class,
        ]);

        $m = $tag->newModel;
        $m->name = 'foo';
        $this->assertSame(true, $m->save());

        $uo = new NgRestModelFixture([
            'modelClass' => UserOnline::class,
        ]);

        $uoModel = $uo->newModel;
        $uoModel->user_id = 1;
        $uoModel->last_timestamp = time();
        $uoModel->invoken_route = 'foobar';
        $uoModel->save();

        $this->assertTrue($m->toggleRelationByModel($uoModel));
        
        // get relations
        $this->assertSame('1', $m->getTagRelations()->count());

        // untoggle
        $this->assertTrue($m->toggleRelationByModel($uoModel));

        // get relations
        // get relations
        $this->assertSame('0', $m->getTagRelations()->count());

    }

    public function testRelationMethods()
    {
        $this->createNgRestLogFixture();

        $tag = new NgRestModelFixture([
            'modelClass' => Tag::class,
        ]);

        $rel = new ActiveRecordFixture([
            'modelClass' => TagRelation::class,
        ]);

        $this->assertSame(3, TagRelation::batchUpdateRelations([1,2,3], 'foobar', 1));

        // distinct

        $values = TagRelation::getDistinctDataForTable('foobar', true);
        $this->assertSame(3, count($values));

        $this->assertSame(3, TagRelation::batchUpdateRelations([1,2,3], 'foobar', 1));
    }

    public function testAfterValidateTableName()
    {
        $rel = new ActiveRecordFixture([
            'modelClass' => TagRelation::class,
        ]);

        $model = new TagRelation();
        $model->table_name = '{{%foobar}}';

        $model->validate(['table_name']);
        $this->assertSame('foobar', $model->table_name);
    }
}