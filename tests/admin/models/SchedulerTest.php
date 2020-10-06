<?php

namespace luya\admin\tests\admin\models;

use luya\Exception;
use admintests\AdminModelTestCase;
use luya\admin\models\Scheduler;
use luya\admin\models\Tag;
use luya\testsuite\fixtures\ActiveRecordFixture;
use luya\testsuite\fixtures\NgRestModelFixture;

class ScheulderTest extends AdminModelTestCase
{
    /**
     * @var ActiveRecordFixture
     */
    protected $fixture;

    public function afterSetup()
    {
        parent::afterSetup();
        $this->fixture = new ActiveRecordFixture([
            'modelClass' => Scheduler::class,
        ]);

        new NgRestModelFixture([
            'modelClass' => Tag::class,
        ]);
    }

    public function testNotFoundModel()
    {
        $model = $this->fixture->newModel;
        $model->model_class = Tag::class;
        $model->target_attribute_name = 'name';
        $model->primary_key = 1;
        $this->expectException(Exception::class);
        $model->triggerJob();
    }

    public function testUnableToSaveDueToValidationError()
    {
        $this->createAdminNgRestLogFixture();
        $this->createAdminLangFixture();

        $tag = new Tag();
        $tag->name = 'unique';
        $tag->save();

        $model = $this->fixture->newModel;
        $model->model_class = Tag::class;
        $model->target_attribute_name = 'name';
        $model->primary_key = 1;
        $this->expectException(Exception::class);
        $model->triggerJob();
    }

    public function testPushToQueue()
    {
        $this->createAdminQueueTable();
        $this->createAdminQueueLogFixture();
        $this->createAdminNgRestLogFixture();
        $model = $this->fixture->newModel;
        $model->model_class = Tag::class;
        $model->target_attribute_name = 'name';
        $model->primary_key = 1;
        $this->assertEmpty($model->pushQueue());
    }
}