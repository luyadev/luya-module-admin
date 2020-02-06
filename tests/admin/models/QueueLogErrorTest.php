<?php

namespace luya\admin\tests\admin\models;

use admintests\AdminModelTestCase;
use luya\admin\models\QueueLogError;
use luya\testsuite\fixtures\NgRestModelFixture;
use luya\testsuite\traits\AdminDatabaseTableTrait;

class QueueLogErrorTest extends AdminModelTestCase
{
    use AdminDatabaseTableTrait;

    public function testModel()
    {
        $fixture = new NgRestModelFixture(['modelClass' => QueueLogError::class]);
        $this->createAdminNgRestLogFixture();
        $this->assertTrue(is_array($fixture->newModel->attributeLabels()));

        $model = $fixture->newModel;

        $model->queue_log_id = 1;
        $model->message = 'foo';
        $this->assertTrue($model->save());
    }
}
