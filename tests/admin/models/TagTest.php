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

        // untoggle

        // get relations

    }
}