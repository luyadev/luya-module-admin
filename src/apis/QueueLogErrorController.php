<?php

namespace luya\admin\apis;

/**
 * Queue Log Error Controller.
 *
 * File has been created with `crud/create` command.
 */
class QueueLogErrorController extends \luya\admin\ngrest\base\Api
{
    /**
     * {@inheritDoc}
     */
    public $modelClass = 'luya\admin\models\QueueLogError';

    /**
     * {@inheritDoc}
     */
    public $truncateAction = true;
}
