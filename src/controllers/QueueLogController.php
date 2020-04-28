<?php

namespace luya\admin\controllers;

use luya\admin\Module;

/**
 * Queue Log Controller.
 *
 * File has been created with `crud/create` command.
 */
class QueueLogController extends \luya\admin\ngrest\base\Controller
{
    /**
     * {@inheritDoc}
     */
    public $modelClass = 'luya\admin\models\QueueLog';

    /**
     * {@inheritDoc}
     */
    public function getDescription()
    {
        return Module::t('queuelog_controller_description');
    }
}
