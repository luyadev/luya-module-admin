<?php

namespace luya\admin\tests\admin\jobs;

use admintests\AdminConsoleSqLiteTestCase;
use luya\admin\jobs\ScheduleJob;
use luya\admin\models\Scheduler;
use luya\admin\models\Tag;
use luya\testsuite\fixtures\NgRestModelFixture;
use luya\testsuite\traits\AdminDatabaseTableTrait;
use yii\base\InvalidCallException;

class ScheduleJobTest extends AdminConsoleSqLiteTestCase
{
    use AdminDatabaseTableTrait;

    public function testExectue()
    {
        $this->createAdminLangFixture([
            1 => [
                'id' => 1,
                'name' => 'en',
                'short_code' => 'en',
                'is_default' => 1,
                'is_deleted' => 0,
            ]
        ]);

        new NgRestModelFixture([
            'modelClass' => Tag::class,
            'fixtureData' => [
                1 => [
                    'id' => 1,
                    'name' => 'aaa',
                    'translation' => '{"de":"aaa","en":"bbb"}',
                ]
            ]
        ]);
        new NgRestModelFixture([
            'modelClass' => Scheduler::class,
            'fixtureData' => [
                1 => [
                    'id' => 1,
                    'model_class' => Tag::class,
                    'primary_key' => 1,
                    'target_attribute_name' => 'name',
                    'new_attribute_value' => 'foobar',
                ]
            ]
        ]);

        $job = new ScheduleJob();
        $job->schedulerId = 1;

        $job->execute($this->app->adminqueue);

        $tag = Tag::findOne(1);

        $this->assertSame('foobar', $tag->name);
    }

    public function testNotFoundScheduler()
    {
        new NgRestModelFixture([
            'modelClass' => Scheduler::class,
        ]);
        $job = new ScheduleJob();
        $job->schedulerId = 2;

        $this->expectException(InvalidCallException::class);
        $job->execute($this->app->adminqueue);
    }
}
