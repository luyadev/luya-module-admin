<?php

namespace luya\admin\jobs;

use luya\admin\models\Scheduler;
use yii\base\BaseObject;
use yii\base\InvalidCallException;
use yii\queue\JobInterface;

/**
 * The Scheduler Job
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 2.0.0
 */
class ScheduleJob extends BaseObject implements JobInterface
{
    /**
     * @var integer The integer value of the scheduler table job id.
     */
    public $schedulerId;

    /**
     * {@inheritDoc}
     */
    public function execute($queue)
    {
        $model = Scheduler::findOne($this->schedulerId);

        // ensure delete schedule jobs wont trigger an error.
        if ($model) {
            return $model->triggerJob();
        }

        // throwing an exception will turn is_error on queue log to 1
        throw new InvalidCallException("Unable to find the given scheduler job with id '{$this->schedulerId}'.");
    }
}
