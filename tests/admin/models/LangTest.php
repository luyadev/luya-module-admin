<?php

namespace luya\admin\tests\admin\models;

use admintests\AdminModelTestCase;
use luya\admin\models\Lang;
use luya\admin\models\NgrestLog;
use luya\testsuite\fixtures\NgRestModelFixture;

class LangTest extends AdminModelTestCase
{
    public function testEvents()
    {
        $fixture = new NgRestModelFixture([
            'modelClass' => Lang::class,
        ]);

        $log = new NgRestModelFixture([
            'modelClass' => NgrestLog::class,
        ]);

        /** @var Lang $model */
        $model = $fixture->newModel;
        $model->short_code = 'fr';
        $model->name = 'Francais';
        $model->is_default = 1;
        $model->is_deleted = 0;

        $this->assertTrue($model->validate());
        $this->assertTrue($model->insert());

        $model->short_code = 'en';
        $model->is_default = 0;
        $this->assertSame(1, $model->update());

        $this->assertTrue($model->delete());
    }
}
