<?php

namespace luya\admin\tests\admin\models;

use admintests\AdminModelTestCase;
use luya\admin\models\Config;
use luya\admin\models\Scheduler;
use luya\admin\models\Tag;
use luya\Exception;
use luya\testsuite\fixtures\ActiveRecordFixture;
use luya\testsuite\fixtures\NgRestModelFixture;

class SchedulerTest extends AdminModelTestCase
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


        $this->createAdminLangFixture([
            1 => [
                'id' => 1,
                'name' => 'en',
                'short_code' => 'en',
                'is_default' => 1,
                'is_deleted' => 0,
            ]
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
        new NgRestModelFixture(['modelClass' => Config::class]);
        $model = $this->fixture->newModel;
        $model->model_class = Tag::class;
        $model->target_attribute_name = 'name';
        $model->primary_key = 1;
        $this->assertEmpty($model->pushQueue());
    }
}
