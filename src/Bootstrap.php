<?php

namespace luya\admin;

use Yii;
use yii\base\BootstrapInterface;
use luya\traits\CacheableTrait;
use luya\admin\models\Config;
use yii\base\Application;

/**
 * CMS Bootstrap.
 *
 * The CMS bootstrap class injects the cms specific url rules
 *
 * + {{luya\cms\frontend\components\RouteBehaviorUrlRule}}
 * + {{luya\cms\frontend\components\CatchAllUrlRule}}
 *
 * And changes the behavior if an exception appears in order to redirect users to a custom cms page.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
final class Bootstrap implements BootstrapInterface
{
    use CacheableTrait;

    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        $app->on(Application::EVENT_BEFORE_REQUEST, [$this, 'runQueueJob']);
    }

    public function runQueueJob($event)
    {
        if (!$event->sender->request->isConsoleRequest && !$event->sender->request->isAdmin) {
            // use cache to ensure this will run only every 30min
            $this->getOrSetHasCache(['admin', 'bootstrap', 'queue'], function() {
                $timestamp = Config::get(Config::CONFIG_QUEUE_TIMESTAMP);
                // if last execution has NOT been done previously (maybe trough a cronjob)
                if ((time() - $timestamp) > (60*25)) {
                    Yii::debug('"Fake-Cronjob" queue run execution.');
                    Yii::$app->adminqueue->run(false);
                    Config::set(Config::CONFIG_QUEUE_TIMESTAMP, time());
                }

                return $timestamp;
            }, 60*30);
        }
    }
}