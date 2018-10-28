<?php

namespace luya\admin\commands;

use Yii;
use luya\console\Command;
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
 * @since 1.3.0
 */
class QueueController extends Command
{
    /**
     * Run the queue jobs.
     */
    public function actionIndex()
    {
        Yii::$app->adminqueue->run(false);

        return ExitCode::OK;
    }
}