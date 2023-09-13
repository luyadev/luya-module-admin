<?php

namespace luya\admin;

use luya\admin\models\Config;
use luya\traits\CacheableTrait;
use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;

/**
 * Admin Bootstrap
 *
 * The main purpose of this Bootstrap files is to have an option to run the queue command
 * trough a "fake cronjob".
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 2.0.0
 */
final class Bootstrap implements BootstrapInterface
{
    use CacheableTrait;

    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        /** @var \luya\admin\Module $admin */
        $admin = $app->getModule('admin');

        // boot the queue job only if enabled and module available.
        if ($admin && $admin->autoBootstrapQueue) {
            $app->on(Application::EVENT_BEFORE_REQUEST, [$this, 'runQueueJob']);
        }
    }

    /**
     * Evaluate whether the current queue job should be run or not.
     *
     * @param \yii\base\Event $event
     */
    public function runQueueJob($event)
    {
        if (!$event->sender->request->isConsoleRequest) {
            // use cache to ensure this will run only every 30min
            $this->getOrSetHasCache(['admin', 'bootstrap', 'queue'], function () {
                $timestamp = Config::get(Config::CONFIG_QUEUE_TIMESTAMP);
                // if last execution has NOT been done previously (maybe trough a cronjob)
                if ((time() - $timestamp) > (60 * 25)) {
                    Yii::debug('"Fake-Cronjob" queue run execution.');
                    Yii::$app->adminqueue->run(false);
                    Config::set(Config::CONFIG_QUEUE_TIMESTAMP, time());
                }

                return $timestamp;
            }, 60 * 30);
        }
    }
}
