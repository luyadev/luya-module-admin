<?php

namespace luya\admin\controllers;

/**
 * Queue Log Error Controller.
 *
 * File has been created with `crud/create` command.
 */
class QueueLogErrorController extends \luya\admin\ngrest\base\Controller
{
    /**
     * @var string The path to the model which is the provider for the rules and fields.
     */
    public $modelClass = 'luya\admin\models\QueueLogError';

    /**
     * {@inheritDoc}
     */
    public $clearButton = true;
}
