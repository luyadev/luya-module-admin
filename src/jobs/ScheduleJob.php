<?php

namespace luya\admin\jobs;

use yii\queue\JobInterface;
use luya\admin\models\Scheduler;
use yii\base\BaseObject;

class ScheduleJob extends BaseObject implements JobInterface
{
    public $schedulerId;

    public function execute($queue)
    {
        $model = Scheduler::findOne($this->schedulerId);
        $model->triggerJob();
    }
}
