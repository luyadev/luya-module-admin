<?php

namespace luya\admin\behaviors;

use Yii;
use yii\base\Behavior;
use yii\queue\Queue;
use yii\queue\PushEvent;
use yii\queue\ExecEvent;
use yii\queue\JobEvent;
use luya\admin\models\QueueLog;
use yii\queue\JobInterface;

/**
 * Log Behavior.
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 * @since 2.0.0
 */
class QueueLogBehavior extends Behavior
{
    /**
     * @var Queue
     * @inheritdoc
     */
    public $owner;


    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            Queue::EVENT_AFTER_PUSH => 'afterPush',
            Queue::EVENT_BEFORE_EXEC => 'beforeExec',
            Queue::EVENT_AFTER_EXEC => 'afterExec',
            Queue::EVENT_AFTER_ERROR => 'afterError',
        ];
    }

    /**
     * @param PushEvent $event
     */
    public function afterPush(PushEvent $event)
    {
        $log = new QueueLog();
        $log->queue_id = $event->id;
        $log->push_timestamp = time();
        $log->title = $this->getJobTitle($event);
        $log->save();
    }

    /**
     * @param ExecEvent $event
     */
    public function beforeExec(ExecEvent $event)
    {
        $log = QueueLog::findOne(['queue_id' => $event->id]);

        if ($log) {
            $log->updateAttributes(['run_timestamp' => time()]);
        }
    }

    /**
     * @param ExecEvent $event
     */
    public function afterExec(ExecEvent $event)
    {
        $log = QueueLog::findOne(['queue_id' => $event->id]);

        if ($log) {
            $log->updateAttributes(['end_timestamp' => time(), 'is_error' => false]);
        }
    }

    /**
     * @param ExecEvent $event
     */
    public function afterError(ExecEvent $event)
    {
        $log = QueueLog::findOne(['queue_id' => $event->id]);

        if ($log) {
            $log->updateAttributes(['end_timestamp' => time(), 'is_error' => true]);
        }
    }
    /**
     * @param JobEvent $event
     * @return string
     * @since 2.0.2
     */
    protected function getJobTitle(JobEvent $event)
    {
        return $event->job instanceof JobInterface ? get_class($event->job) : 'unknown job';
    }

    /**
     * @param ExecEvent $event
     * @return string
     * @since 2.0.2
     */
    protected function getExecTitle(ExecEvent $event)
    {
        $title = $this->getJobTitle($event);
        $extra = "attempt: $event->attempt";
        if ($pid = $event->sender->getWorkerPid()) {
            $extra .= ", PID: $pid";
        }
        return "$title ($extra)";
    }
}
