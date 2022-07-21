<?php

namespace luya\admin\commands;

use luya\admin\models\Config;
use luya\console\Command;
use Yii;
use yii\console\ExitCode;

/**
 * Run jobs inside the Queue.
 *
 * You might schedule this command trough cronjob every 5 minutes (or lower).
 *
 * ```sh
 * ./vendor/bin/luya admin/queue
 * ```
 *
 * > As alternative to the admin/queue command you can enable {{luya\admin\Module::$autoBootstrapQueue}} "fake cronjob" behavior.
 *
 * @see {{luya\admin\Module::$autoBootstrapQueue}}
 * @author Basil Suter <basil@nadar.io>
 * @since 2.0.0
 */
class QueueController extends Command
{
    /**
     * Run the queue jobs.
     */
    public function actionIndex()
    {
        Yii::$app->adminqueue->run(false);
        Config::set(Config::CONFIG_QUEUE_TIMESTAMP, time());
        return ExitCode::OK;
    }
}
