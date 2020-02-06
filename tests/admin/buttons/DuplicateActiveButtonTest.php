<?php

namespace admintests\admin\buttons;

use admintests\AdminModelTestCase;
use luya\admin\buttons\DuplicateActiveButton;
use luya\admin\models\Group;
use luya\admin\models\Tag;
use luya\testsuite\fixtures\NgRestModelFixture;
use luya\testsuite\traits\AdminDatabaseTableTrait;

class DuplicateActiveButtonTestt extends AdminModelTestCase
{
    use AdminDatabaseTableTrait;

    public function testCopyDuplicateMode()
    {
        $btn = new DuplicateActiveButton();
    
        $this->createAdminNgRestLogFixture();
        $fixtureGroup = new NgRestModelFixture([
            'schema' => [
                'id' => 'pk',
                'user_id' => 'int(11)',
                'group_id' => 'int(11)',
            ],
            'tableName' => 'admin_user_group',
        ]);
        $fixture = new NgRestModelFixture([
            'removeSafeAttributes' => true,
            'modelClass' => Group::class,
        ]);

        $model = $fixture->newModel;
        $model->name = 'foobar';
        $model->is_deleted = false;
        $model->text = 'barfoo';
        $model->users = [1];
        $model->save();

        $this->assertSame("1", $model->id);

        // wont work as alias is unique
        $this->assertSame([
            'success' => true,
            'message' => 'active_button_duplicate_success',
            'events' => [
                'loadList',
            ],
        ], $btn->handle($model));
    }

    public function testCopyDuplicateModelError()
    {
        $btn = new DuplicateActiveButton();
    
        $this->createAdminNgRestLogFixture();
        $fixture = new NgRestModelFixture([
            'modelClass' => Tag::class,
        ]);

        $model = $fixture->newModel;
        $model->name = 'foobar';
        $model->translation = ['de' => 'DEBAR', 'en' => 'ENBAR'];
        $model->save();

        $this->assertSame("1", $model->id);

        // wont work as alias is unique
        $this->assertSame([
            'success' => false,
            'message' => 'active_button_duplicate_error',
            'events' => [],
        ], $btn->handle($model));
    }

    public function testCopyDuplicateModelErrorNotFound()
    {
        $btn = new DuplicateActiveButton();
    
        $this->createAdminNgRestLogFixture();
        $fixture = new NgRestModelFixture([
            'modelClass' => Tag::class,
        ]);

        $model = $fixture->newModel;

        // wont work as alias is unique
        $this->assertSame([
            'success' => false,
            'message' => 'active_button_duplicate_error',
            'events' => [],
        ], $btn->handle($model));
    }
}
