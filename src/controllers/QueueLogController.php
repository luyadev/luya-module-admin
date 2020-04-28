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
     * @var string The path to the model which is the provider for the rules and fields.
     */
    public $modelClass = 'luya\admin\models\QueueLog';

    public function getDescription()
    {
        return Module::t('queuelog_controller_description');
    }
}
